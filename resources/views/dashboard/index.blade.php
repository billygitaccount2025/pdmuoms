@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="content-header">
        <h1>Welcome back, {{ Auth::user()->fname ?? 'User' }}! 👋</h1>
        <p>Here's what's happening with your projects today.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Stat Card 1 -->
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin: 0;">Active Projects</p>
                    <h2 style="color: #002C76; font-size: 28px; margin: 8px 0 0;">12</h2>
                </div>
                <div style="font-size: 40px; color: #002C76; opacity: 0.2;">
                    <i class="fas fa-project-diagram"></i>
                </div>
            </div>
        </div>
        
        <!-- Stat Card 2 -->
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin: 0;">Completed Tasks</p>
                    <h2 style="color: #10b981; font-size: 28px; margin: 8px 0 0;">48</h2>
                </div>
                <div style="font-size: 40px; color: #10b981; opacity: 0.2;">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <!-- Stat Card 3 -->
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin: 0;">Pending Reports</p>
                    <h2 style="color: #f59e0b; font-size: 28px; margin: 8px 0 0;">5</h2>
                </div>
                <div style="font-size: 40px; color: #f59e0b; opacity: 0.2;">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>
        
        <!-- Stat Card 4 -->
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin: 0;">Team Members</p>
                    <h2 style="color: #8b5cf6; font-size: 28px; margin: 8px 0 0;">24</h2>
                </div>
                <div style="font-size: 40px; color: #8b5cf6; opacity: 0.2;">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity Section -->
    <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="color: #002C76; font-size: 18px; margin: 0 0 20px;">Recent Activity</h2>
        
        <div style="border-left: 3px solid #002C76; padding-left: 20px; margin-bottom: 20px;">
            <p style="color: #002C76; font-weight: 600; margin: 0;">Project Alpha Started</p>
            <p style="color: #6b7280; font-size: 14px; margin: 4px 0 0;">Today at 10:30 AM</p>
        </div>
        
        <div style="border-left: 3px solid #10b981; padding-left: 20px; margin-bottom: 20px;">
            <p style="color: #10b981; font-weight: 600; margin: 0;">Task Completed: Report Review</p>
            <p style="color: #6b7280; font-size: 14px; margin: 4px 0 0;">Yesterday at 3:15 PM</p>
        </div>
        
        <div style="border-left: 3px solid #f59e0b; padding-left: 20px;">
            <p style="color: #f59e0b; font-weight: 600; margin: 0;">New Team Member Added</p>
            <p style="color: #6b7280; font-size: 14px; margin: 4px 0 0;">2 days ago</p>
        </div>
    </div>
@endsection
