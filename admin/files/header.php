<header class="top-header">
    <style>
        .top-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            height: 70px;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .menu-toggle {
            font-size: 20px;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu-toggle:hover {
            color: #3b82f6;
            transform: scale(1.1);
        }

        .header-left span {
            font-size: 14px;
            color: #64748b;
            letter-spacing: 0.3px;
        }

        .header-left strong {
            color: #0f172a;
            font-weight: 700;
            margin-left: 5px;
            background: linear-gradient(to right, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Admin Button UI */
        .header-right {
            display: flex;
            align-items: center;
        }

        .admin-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 22px;
            background: #0f172a;
            /* Sophisticated Dark Theme */
            color: #ffffff;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            border: 1px solid #1e293b;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .admin-btn:hover {
            background: #3b82f6;
            border-color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }

        .admin-btn i {
            font-size: 14px;
            color: #3b82f6;
            /* Accent color for the icon */
            transition: all 0.3s ease;
        }

        .admin-btn:hover i {
            color: #ffffff;
        }
    </style>

    <div class="header-left">
        <div class="menu-toggle">
            <i class="fas fa-bars-staggered"></i>
        </div>
        <span>Welcome back, <strong>ADMIN</strong></span>
    </div>

    <div class="header-right">
        <button class="admin-btn">
            <i class="fas fa-shield-halved"></i>
            <span>Admin Panel</span>
        </button>
    </div>
</header>