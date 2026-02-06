<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - DILG-CAR PDMU</title>
    <link rel="icon" type="image/png" href="/DILG-Logo.png">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Facebook Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-image: url('/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            overflow-x: hidden;
        }

        body.sidebar-open {
            overflow: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, #002C76 0%, #003d99 100%);
            padding: 20px;
            overflow-y: auto;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
        }
        
        .sidebar.collapsed {
            width: 0;
            padding: 0;
            overflow: hidden;
            box-shadow: none;
        }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }
        
        .sidebar-logo {
            width: 50px;
            height: 50px;
            margin-right: 12px;
        }
        
        .sidebar-title {
            color: white;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .sidebar-title small {
            display: block;
            font-size: 12px;
            font-weight: 400;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 8px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .sidebar-menu a:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            padding-left: 20px;
        }
        
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.25);
            color: white;
            font-weight: 600;
        }
        
        .sidebar-menu i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }

        /* Submenu Styles */
        .submenu {
            list-style: none;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 6px;
            overflow: hidden;
            margin-top: 8px;
        }

        .submenu li {
            margin: 0;
        }

        .submenu a {
            display: flex;
            align-items: center;
            padding: 10px 16px 10px 48px !important;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .submenu a:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            padding-left: 52px !important;
        }

        .submenu a.active {
            background-color: rgba(255, 255, 255, 0.25);
            color: white;
            font-weight: 600;
        }
        
        /* Topbar Styles */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 999;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .topbar.with-sidebar {
            padding-left: 280px;
        }
        
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
            color: #002C76;
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            z-index: 1001;
            position: relative;
        }
        
        .toggle-btn:hover {
            background-color: #f0f0f0;
            color: #003d99;
        }
        
        .toggle-btn:active {
            transform: scale(0.95);
        }
        
        .topbar-title {
            font-size: 18px;
            font-weight: 600;
            color: #002C76;
            margin: 0;
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        /* Profile Dropdown */
        .profile-dropdown {
            position: relative;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-direction: row-reverse;
        }
        
        .profile-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #002C76 0%, #003d99 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .profile-icon:hover {
            border-color: #002C76;
            box-shadow: 0 4px 12px rgba(0, 44, 118, 0.2);
        }

        /* Notification Bell */
        .notification-bell {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: transparent;
            color: #002C76;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            transition: all 0.3s ease;
            border: none;
            padding: 0;
        }

        .notification-bell:hover {
            color: #003d99;
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc2626;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            border: 2px solid white;
        }

        .profile-menu {
            position: absolute;
            top: 50px;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            display: none;
            z-index: 1001;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        
        .profile-menu.show {
            display: block;
            animation: slideDown 0.2s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .profile-menu-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        
        .profile-menu-name {
            font-weight: 600;
            color: #002C76;
            font-size: 14px;
        }
        
        .profile-menu-email {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }
        
        .profile-menu-item {
            padding: 12px 20px;
            color: #374151;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
            background: none;
            cursor: pointer;
            width: 100%;
            text-align: left;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .profile-menu-item:hover {
            background-color: #f3f4f6;
            color: #002C76;
        }
        
        .profile-menu-item.logout {
            color: #dc2626;
            border-top: 1px solid #e5e7eb;
        }
        
        .profile-menu-item.logout:hover {
            background-color: #fef2f2;
        }
        
        /* Main Content */
        .main-content {
            margin-top: 70px;
            margin-left: 250px;
            padding: 30px;
            min-height: calc(100vh - 70px);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .main-content.with-sidebar {
            margin-left: 250px;
        }
        
        .main-content:not(.with-sidebar) {
            margin-left: 0;
        }
        
        .content-header {
            margin-bottom: 30px;
        }
        
        .content-header h1 {
            color: #002C76;
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .content-header p {
            color: #6b7280;
            font-size: 14px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 220px;
            }
            
            .sidebar.collapsed {
                transform: translateX(-220px);
                width: 0;
            }
            
            .topbar {
                padding: 0 15px;
            }
            
            .topbar.with-sidebar {
                padding-left: 15px;
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }
            
            .main-content.with-sidebar {
                margin-left: 220px;
            }
            
            .topbar-title {
                font-size: 16px;
            }
            
            .sidebar-title {
                font-size: 14px;
            }
            
            .content-header h1 {
                font-size: 24px;
            }

            .content-header {
                flex-wrap: wrap;
                gap: 12px;
            }

            .main-content div[style*="display: flex"][style*="justify-content: space-between"] {
                flex-wrap: wrap;
                gap: 12px;
            }

            .main-content div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }

            .main-content div[style*="grid-template-columns: repeat(3"] {
                grid-template-columns: 1fr !important;
            }

            .main-content div[style*="grid-template-columns: repeat(2"] {
                grid-template-columns: 1fr !important;
            }

            .main-content div[style*="grid-template-columns: repeat(auto-fit"] {
                grid-template-columns: 1fr !important;
            }

            .main-content div[style*="grid-template-columns: minmax"] {
                grid-template-columns: 1fr !important;
            }
        }
        
        @media (max-width: 480px) {
            .sidebar {
                width: 80%;
                z-index: 1100;
                max-width: 320px;
            }
            
            .sidebar.collapsed {
                transform: translateX(-100%);
            }
            
            .topbar {
                padding: 0 12px;
                height: 60px;
            }
            
            .topbar.with-sidebar {
                padding-left: 12px;
            }
            
            .topbar-left {
                gap: 10px;
            }
            
            .toggle-btn {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
            
            .topbar-title {
                display: none;
            }
            
            .topbar-right {
                gap: 15px;
            }
            
            .main-content {
                margin-top: 60px;
                margin-left: 0;
                padding: 15px 12px;
                min-height: calc(100vh - 60px);
            }
            
            .main-content.with-sidebar {
                margin-left: 0;
            }
            
            .content-header {
                margin-bottom: 20px;
            }
            
            .content-header h1 {
                font-size: 20px;
                margin-bottom: 6px;
            }
            
            .content-header p {
                font-size: 13px;
            }
            
            .sidebar-header {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }
            
            .sidebar-logo {
                width: 40px;
                height: 40px;
                margin-right: 8px;
            }
            
            .sidebar-title {
                font-size: 12px;
            }
            
            .sidebar-title small {
                font-size: 10px;
            }
            
            .sidebar-menu a {
                padding: 10px 12px;
                font-size: 13px;
            }
            
            .sidebar-menu i {
                width: 18px;
                margin-right: 10px;
            }
            
            .profile-icon {
                width: 36px;
                height: 36px;
                font-size: 18px;
            }
            
            .profile-menu {
                right: -5px;
                min-width: 170px;
                font-size: 13px;
            }
            
            .profile-menu-item {
                padding: 10px 16px;
                font-size: 13px;
            }
            
            .profile-menu-header {
                padding: 12px 16px;
            }
            
            .profile-menu-name {
                font-size: 13px;
            }
            
            .profile-menu-email {
                font-size: 11px;
            }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('DILG-Logo.png') }}" alt="DILG Logo" class="sidebar-logo">
            <div class="sidebar-title">
                DILG-CAR
                <small>PDMU OPERATIONS MANAGEMENT SYSTEM (POMS)</small>
            </div>
            <!-- Close Button for Mobile -->
            <button id="closeSidebarBtn" style="display: none; position: absolute; right: 15px; top: 20px; background: none; border: none; color: white; font-size: 24px; cursor: pointer; width: 40px; height: 40px; padding: 0; border-radius: 6px; transition: all 0.3s ease;" title="Close Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('dashboard') }}" class="@if(Route::currentRouteName() == 'dashboard') active @endif">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#" class="@if(Route::currentRouteName() == 'projects') active @endif" onclick="toggleSubmenu(event, 'projectsMenu')">
                    <i class="fas fa-project-diagram"></i>
                    <span>Projects</span>
                    <i class="fas fa-chevron-down" style="margin-left: auto; font-size: 12px;"></i>
                </a>
                <ul id="projectsMenu" class="submenu" style="display: none;">
                    <li>
                        <a href="{{ route('projects.locally-funded') }}" class="@if(Route::currentRouteName() == 'projects.locally-funded') active @endif">
                            <i class="fas fa-hand-holding-usd"></i>
                            <span>Locally Funded Projects</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('projects.rlip-lime') }}" class="@if(Route::currentRouteName() == 'projects.rlip-lime') active @endif">
                            <i class="fas fa-leaf"></i>
                            <span>RLIP/LIME-20% Development Fund</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#" class="@if(Route::currentRouteName() == 'reports') active @endif" onclick="toggleSubmenu(event, 'reportsMenu')">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
                    <i class="fas fa-chevron-down" style="margin-left: auto; font-size: 12px;"></i>
                </a>
                <ul id="reportsMenu" class="submenu" style="display: none;">
                    <li>
                        <a href="{{ route('fund-utilization.index') }}" class="@if(Route::currentRouteName() == 'fund-utilization.index') active @endif">
                            <i class="fas fa-coins"></i>
                            <span>Fund Utilization Report</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('local-project-monitoring-committee.index') }}" class="@if(Route::currentRouteName() == 'local-project-monitoring-committee.index') active @endif">
                            <i class="fas fa-users-cog"></i>
                            <span>Local Project Monitoring Committee</span>
                        </a>
                    </li>
                </ul>
            </li>
            @if(Auth::user()->role === 'superadmin')
            <li>
                <a href="{{ route('users.index') }}" class="@if(Route::currentRouteName() == 'users.index') active @endif">
                    <i class="fas fa-user-shield"></i>
                    <span>User Management</span>
                </a>
            </li>
            @endif
        </ul>
    </aside>
    
    <!-- Top Navigation Bar -->
    <div class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="toggle-btn" id="toggleBtn" title="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="topbar-title" id="pageTitle">@yield('page-title', 'Dashboard')</h1>
        </div>
        
        <div class="topbar-right">
            <div class="profile-dropdown">
                <div class="profile-icon" id="profileIcon" title="Profile">
                    <i class="fas fa-user"></i>
                </div>
                <button class="notification-bell" id="notificationBell" title="Notifications">
                    <i class="fas fa-bell"></i>
                    @php
                        $unreadNotifications = \Illuminate\Support\Facades\DB::table('tbnotifications')
                            ->where('user_id', Auth::id())
                            ->whereNull('read_at')
                            ->count();
                    @endphp
                    @if($unreadNotifications > 0)
                        <span class="notification-badge">{{ $unreadNotifications }}</span>
                    @endif
                </button>
                <div class="profile-menu" id="profileMenu">
                    <div class="profile-menu-header">
                        <div class="profile-menu-name">{{ Auth::user()->fname ?? 'User' }} {{ Auth::user()->lname ?? '' }}</div>
                        <div class="profile-menu-email">{{ Auth::user()->emailaddress ?? 'user@example.com' }}</div>
                    </div>
                    <a href="{{ route('profile.show') }}" class="profile-menu-item">
                        <i class="fas fa-user-circle"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="{{ route('password.show') }}" class="profile-menu-item">
                        <i class="fas fa-lock"></i>
                        <span>Change Password</span>
                    </a>
                    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <button class="profile-menu-item logout" onclick="document.getElementById('logoutForm').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <main class="main-content" id="mainContent">
        @yield('content')
    </main>
    
    <script>
        // Sidebar Toggle
        const toggleBtn = document.getElementById('toggleBtn');
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const topbar = document.getElementById('topbar');
        const body = document.body;
        
        // Check if sidebar should start collapsed (from localStorage)
        let sidebarExpanded = localStorage.getItem('sidebarExpanded') !== 'false';
        
        // Check if mobile
        function isMobile() {
            return window.innerWidth <= 480;
        }
        
        // Initialize sidebar state
        function updateSidebarState() {
            if (sidebarExpanded) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.add('with-sidebar');
                topbar.classList.add('with-sidebar');
                
                // Show close button on mobile
                if (isMobile() && closeSidebarBtn) {
                    closeSidebarBtn.style.display = 'block';
                }
                
                if (isMobile()) {
                    body.classList.add('sidebar-open');
                }
            } else {
                sidebar.classList.add('collapsed');
                mainContent.classList.remove('with-sidebar');
                topbar.classList.remove('with-sidebar');
                
                // Hide close button when sidebar is collapsed
                if (closeSidebarBtn) {
                    closeSidebarBtn.style.display = 'none';
                }
                body.classList.remove('sidebar-open');
            }
            localStorage.setItem('sidebarExpanded', sidebarExpanded);
        }
        
        // Initialize on page load
        updateSidebarState();
        
        // Toggle button click handler
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            sidebarExpanded = !sidebarExpanded;
            updateSidebarState();
        });

        // Close sidebar button click handler
        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                sidebarExpanded = false;
                updateSidebarState();
            });

            // Also on hover for better UX
            closeSidebarBtn.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
            });

            closeSidebarBtn.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'transparent';
            });
        }

        // Close sidebar when clicking on content area on mobile
        if (isMobile()) {
            mainContent.addEventListener('click', function() {
                if (sidebarExpanded) {
                    sidebarExpanded = false;
                    updateSidebarState();
                }
            });

            // Close sidebar on window resize if opening desktop size
            window.addEventListener('resize', function() {
                if (window.innerWidth > 480) {
                    if (!sidebarExpanded) {
                        sidebarExpanded = true;
                        updateSidebarState();
                    }
                    if (closeSidebarBtn) {
                        closeSidebarBtn.style.display = 'none';
                    }
                } else {
                    // On mobile, update close button visibility
                    if (sidebarExpanded && closeSidebarBtn) {
                        closeSidebarBtn.style.display = 'block';
                    }
                }
            });
        }
        
        // Profile Dropdown Toggle
        const profileIcon = document.getElementById('profileIcon');
        const profileMenu = document.getElementById('profileMenu');
        const notificationBell = document.getElementById('notificationBell');
        
        // Toggle submenu function
        function toggleSubmenu(event, submenuId) {
            event.preventDefault();
            const submenu = document.getElementById(submenuId);
            submenu.style.display = submenu.style.display === 'none' ? 'block' : 'none';
        }

        if (profileIcon && profileMenu) {
            profileIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                profileMenu.classList.toggle('show');
            });
        }

        // Close menus when clicking outside
        document.addEventListener('click', function(e) {
            if (profileMenu && profileIcon && !profileMenu.contains(e.target) && !profileIcon.contains(e.target)) {
                profileMenu.classList.remove('show');
            }
        });

        // Confirmation for save/update/delete actions
        (function attachActionConfirms() {
            const defaultMessages = {
                save: 'Are you sure you want to save these changes?',
                delete: 'Are you sure you want to delete this item? This action cannot be undone.'
            };

            function getActionText(el) {
                const text = (el.textContent || el.value || '').trim().toLowerCase();
                return text;
            }

            function hasInlineConfirm(el) {
                const onclick = el.getAttribute('onclick') || '';
                return onclick.includes('confirm(') || onclick.includes('deleteDocument(');
            }

            function formHasInlineConfirm(el) {
                const form = el.closest('form');
                if (!form) return false;
                const onsubmit = form.getAttribute('onsubmit') || '';
                return onsubmit.includes('confirm(');
            }

            function needsAutoConfirm(el) {
                if (!el || el.disabled) return false;
                if (el.dataset && el.dataset.confirmSkip === 'true') return false;
                if (el.dataset && el.dataset.confirm) return true;
                if (hasInlineConfirm(el) || formHasInlineConfirm(el)) return false;
                const text = getActionText(el);
                if (!text) return false;
                const isSave = text.includes('save');
                const isDelete = text.includes('delete');
                return isSave || isDelete;
            }

            function resolveMessage(el) {
                if (el.dataset && el.dataset.confirm) return el.dataset.confirm;
                const text = getActionText(el);
                return text.includes('delete') ? defaultMessages.delete : defaultMessages.save;
            }

            document.addEventListener('click', function(e) {
                const target = e.target.closest('button, input[type="submit"], input[type="button"], a');
                if (!target) return;
                if (!needsAutoConfirm(target)) return;
                const message = resolveMessage(target);
                if (!window.confirm(message)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }
                if (target.dataset) {
                    target.dataset.confirmed = 'true';
                }
            }, true);

            document.addEventListener('submit', function(e) {
                const submitter = e.submitter;
                if (!submitter) return;
                if (submitter.dataset && submitter.dataset.confirmed === 'true') {
                    delete submitter.dataset.confirmed;
                    return;
                }
                if (!needsAutoConfirm(submitter)) return;
                const message = resolveMessage(submitter);
                if (!window.confirm(message)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);
        })();
    </script>
    
    @yield('scripts')
</body>
</html>
