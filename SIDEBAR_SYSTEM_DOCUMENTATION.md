# Role-Based Sidebar System Documentation

## Overview
Sistem sidebar berbasis role yang memisahkan menu navigasi berdasarkan peran user untuk pengalaman yang lebih terorganisir dan user-friendly.

## Structure
```
resources/views/components/sidebar/
├── admin.blade.php          # Sidebar untuk Administrator
├── leader.blade.php         # Sidebar untuk Team Lead
└── user.blade.php          # Sidebar untuk User biasa
```

## Role Detection System

### 1. SidebarController Helper
File: `app/Http/Controllers/SidebarController.php`

#### Methods:
- `getUserRoleInfo()` - Mendapatkan informasi lengkap role user
- `getSidebarConfig()` - Mendapatkan konfigurasi sidebar berdasarkan role
- `getNotificationCount()` - Mendapatkan jumlah notifikasi user
- `getProjectRoles()` - Mendapatkan role user dalam project
- `hasProjectManagerRole()` - Cek apakah user adalah project manager
- `hasRole()` - Cek role spesifik user

### 2. Role Mapping
```php
'admin' => [
    'component' => 'components.sidebar.admin',
    'theme' => 'red',
    'title' => 'System Administration',
    'icon' => '⚙️'
],
'team_lead' => [
    'component' => 'components.sidebar.leader',
    'theme' => 'indigo', 
    'title' => 'Team Leadership',
    'icon' => '👥'
],
'user' => [
    'component' => 'components.sidebar.user',
    'theme' => 'blue',
    'title' => 'Team Member',
    'icon' => '👤'
]
```

## Sidebar Components

### 1. Admin Sidebar (`components.sidebar.admin`)
**Theme:** Red gradient
**Features:**
- System Administration
- User Management  
- Project Management
- Settings & Configuration
- Analytics & Reports
- System Logs

**Target Users:** System administrators

### 2. Leader Sidebar (`components.sidebar.leader`)
**Theme:** Indigo/Purple gradient
**Features:**
- Leadership Dashboard
- Team Management
- Project Assignment
- Performance Tracking
- Resource Planning

**Target Users:** Team leads, project managers dalam sistem

### 3. User Sidebar (`components.sidebar.user`)
**Theme:** Dynamic based on project role
**Features:**
- Personal Dashboard
- My Tasks
- Time Tracking
- My Projects
- Role-specific Tools (Designer/Developer)
- Profile Management

**Dynamic Theming:**
- **Project Manager:** Purple theme (🏆)
- **Designer & Developer:** Indigo theme (🎨💻)
- **Designer Only:** Pink theme (🎨)  
- **Developer Only:** Green theme (💻)
- **Regular User:** Blue theme (👤)

## Implementation

### 1. Layout Integration
File: `resources/views/layout/app.blade.php`
```php
@php
    use App\Http\Controllers\SidebarController;
    $sidebarConfig = SidebarController::getSidebarConfig();
@endphp

@include($sidebarConfig['component'])
```

### 2. Role Detection Logic
```php
// System role (dari tabel users)
$user->role // 'admin', 'team_lead', 'user'

// Project role (dari tabel project_members)
$projectRoles // ['designer', 'developer', 'project_manager']
```

## Benefits

### 1. **Clean Separation of Concerns**
- Setiap role memiliki sidebar terpisah
- Mengurangi kompleksitas kondisional
- Mudah maintenance dan update

### 2. **Better User Experience**
- User hanya melihat menu yang relevan
- Visual theme sesuai dengan role
- Navigasi yang lebih intuitive

### 3. **Scalability**
- Mudah menambah role baru
- Flexible theming system
- Component-based architecture

### 4. **Security**
- Role-based access di level UI
- Reduced menu clutter
- Clear permission boundaries

## Customization

### 1. Adding New Role
1. Buat file sidebar baru: `components/sidebar/newrole.blade.php`
2. Update `SidebarController::getSidebarConfig()`
3. Tambah role mapping dengan theme dan konfigurasi

### 2. Modifying Themes
Edit variabel di masing-masing sidebar component:
```php
$gradientColors = 'from-[color]-900 via-[color]-800 to-[color]-900';
$borderColor = 'border-[color]-700';
$hoverColor = 'hover:bg-[color]-700';
```

### 3. Adding Menu Items
Tambahkan item baru di section navigation yang sesuai dalam sidebar component.

## Usage Examples

### 1. Admin User
- Melihat sidebar merah dengan menu system administration
- Akses ke user management, settings, analytics

### 2. Team Lead 
- Melihat sidebar ungu dengan menu leadership
- Dashboard untuk assign tasks, set priority, track progress
- Akses ke team management tools

### 3. Regular User (Designer)
- Melihat sidebar pink dengan menu designer
- Akses ke design assets, personal tasks
- Portfolio dan creative tools

### 4. Regular User (Developer)  
- Melihat sidebar hijau dengan menu developer
- Akses ke code repository, technical tasks
- Development tools dan resources

## Future Enhancements
1. **Dynamic Menu Loading** - Load menu items from database
2. **Permission-based Menu** - Hide menu berdasarkan specific permissions
3. **Personalization** - Allow user to customize sidebar order
4. **Multi-language Support** - Sidebar internationalization
5. **Theme Customization** - User-selectable sidebar themes