<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectMemberService
{
    /**
     * Tambahkan member ke project
     *
     * @param int $projectId
     * @param int $userId
     * @param string $role
     * @return ProjectMember
     * @throws \Exception
     */
    public function addMember(int $projectId, int $userId, string $role): ProjectMember
    {
        DB::beginTransaction();
        
        try {
            $project = Project::findOrFail($projectId);
            $user = User::findOrFail($userId);
            
            // Validasi business rules
            $this->validateMemberAddition($project, $user);
            
            // Buat member baru
            $member = ProjectMember::create([
                'project_id' => $projectId,
                'user_id' => $userId,
                'role' => $role,
                'joined_at' => now(),
            ]);
            
            // Log activity
            Log::info("Member added to project", [
                'project_id' => $projectId,
                'user_id' => $userId,
                'role' => $role,
                'added_by' => auth()->id(),
            ]);
            
            DB::commit();
            
            return $member->load('user');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to add member: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update role member
     *
     * @param int $memberId
     * @param string $newRole
     * @return ProjectMember
     */
    public function updateMemberRole(int $memberId, string $newRole): ProjectMember
    {
        $member = ProjectMember::findOrFail($memberId);
        
        $member->update(['role' => $newRole]);
        
        Log::info("Member role updated", [
            'member_id' => $memberId,
            'new_role' => $newRole,
            'updated_by' => auth()->id(),
        ]);
        
        return $member->load('user');
    }
    
    /**
     * Hapus member dari project
     *
     * @param int $memberId
     * @return bool
     */
    public function removeMember(int $memberId): bool
    {
        $member = ProjectMember::findOrFail($memberId);
        
        Log::info("Member removed from project", [
            'member_id' => $memberId,
            'project_id' => $member->project_id,
            'user_id' => $member->user_id,
            'removed_by' => auth()->id(),
        ]);
        
        return $member->delete();
    }
    
    /**
     * Validasi apakah user bisa ditambahkan sebagai member
     *
     * @param Project $project
     * @param User $user
     * @throws \Exception
     */
    private function validateMemberAddition(Project $project, User $user): void
    {
        // Validasi: User harus aktif
        if ($user->status !== 'active') {
            throw new \Exception('User tidak aktif dan tidak dapat ditambahkan ke project.');
        }
        
        // Validasi: User harus memiliki role 'user' (bukan admin atau leader)
        if ($user->role !== 'user') {
            throw new \Exception('Hanya user dengan role User yang dapat ditambahkan sebagai anggota project.');
        }
        
        // Validasi: User belum menjadi member di project ini
        $existingMember = ProjectMember::where('project_id', $project->project_id)
            ->where('user_id', $user->user_id)
            ->exists();
            
        if ($existingMember) {
            throw new \Exception('User sudah menjadi anggota project ini.');
        }
        
        // VALIDASI BARU: User dengan role 'user' hanya boleh di 1 project
        $userProjectCount = ProjectMember::where('user_id', $user->user_id)
            ->whereHas('project', function($query) {
                $query->whereNull('deleted_at'); // Only count active projects
            })
            ->count();
            
        if ($userProjectCount > 0) {
            $existingProject = ProjectMember::where('user_id', $user->user_id)
                ->with('project')
                ->first();
            
            $projectName = $existingProject && $existingProject->project 
                ? $existingProject->project->project_name 
                : 'project lain';
                
            throw new \Exception("User {$user->full_name} sudah terdaftar di project \"{$projectName}\". User dengan role User hanya dapat bergabung di 1 project.");
        }
    }
    
    /**
     * Cek apakah user dapat mengelola members dari project
     *
     * @param int $projectId
     * @param int $userId
     * @return bool
     */
    public function canManageMembers(int $projectId, int $userId): bool
    {
        $project = Project::find($projectId);
        
        if (!$project) {
            return false;
        }
        
        // Hanya leader dari project yang bisa mengelola members
        return $project->leader_id === $userId;
    }
    
    /**
     * Get available users yang bisa ditambahkan ke project
     *
     * @param int $projectId
     * @param string|null $searchQuery
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableUsers(int $projectId, ?string $searchQuery = null)
    {
        // Get user IDs yang sudah menjadi member di project ini
        $existingMemberIds = ProjectMember::where('project_id', $projectId)
            ->pluck('user_id')
            ->toArray();
        
        // Get user IDs yang sudah di project lain (untuk user dengan role 'user')
        $usersInOtherProjects = ProjectMember::whereNotIn('project_id', [$projectId])
            ->whereHas('user', function($query) {
                $query->where('role', 'user');
            })
            ->whereHas('project', function($query) {
                $query->whereNull('deleted_at'); // Only active projects
            })
            ->pluck('user_id')
            ->toArray();
        
        // Exclude both: members di project ini dan users yang sudah di project lain
        $excludedUserIds = array_merge($existingMemberIds, $usersInOtherProjects);
        
        $query = User::whereNotIn('user_id', $excludedUserIds)
            ->where('status', 'active')
            ->where('role', 'user'); // Hanya user biasa
        
        // Apply search filter jika ada
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('full_name', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('username', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('email', 'LIKE', "%{$searchQuery}%");
            });
        }
        
        return $query->limit(10)
            ->get(['user_id', 'full_name', 'username', 'email', 'role']);
    }
    
    /**
     * Get project members with statistics
     *
     * @param int $projectId
     * @return array
     */
    public function getProjectMembers(int $projectId): array
    {
        $members = ProjectMember::where('project_id', $projectId)
            ->with('user:user_id,username,full_name,email,role')
            ->orderBy('role')
            ->orderBy('joined_at')
            ->get();
        
        $statistics = [
            'total' => $members->count(),
            'developers' => $members->where('role', 'developer')->count(),
            'designers' => $members->where('role', 'designer')->count(),
        ];
        
        return [
            'members' => $members,
            'statistics' => $statistics,
        ];
    }
}
