<style>
    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 60px 0;
        margin-bottom: 40px;
        border-radius: var(--radius-lg);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="1" fill="white" opacity="0.05"/><circle cx="10" cy="60" r="1" fill="white" opacity="0.05"/><circle cx="90" cy="40" r="1" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        pointer-events: none;
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .hero-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 700;
        margin: 0 0 15px 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: 1.3rem;
        margin: 0 0 20px 0;
        opacity: 0.9;
        font-weight: 300;
    }

    .hero-version {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }

    /* Dashboard Stats */
    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 50px;
    }

    .stat-card {
        background: white;
        padding: 30px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--gray-300), var(--gray-300));
        transition: background 0.3s ease;
    }

    .stat-card.has-data::before {
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-color);
    }

    .stat-card.has-data:hover {
        box-shadow: 0 20px 40px rgba(66, 153, 225, 0.3);
    }

    .stat-icon {
        font-size: 3rem;
        margin-bottom: 15px;
        filter: grayscale(1);
        transition: filter 0.3s ease;
    }

    .stat-card.has-data .stat-icon {
        filter: none;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--gray-400);
        margin-bottom: 8px;
        transition: color 0.3s ease;
    }

    .stat-card.has-data .stat-number {
        color: var(--primary-color);
    }

    .stat-label {
        color: var(--gray-500);
        font-size: 0.95rem;
        font-weight: 500;
    }

    /* Quick Actions */
    .quick-actions {
        margin-bottom: 50px;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .action-card {
        background: white;
        padding: 40px 30px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid var(--gray-200);
        position: relative;
        overflow: hidden;
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(66, 153, 225, 0.05), transparent);
        transform: rotate(45deg);
        transition: transform 0.6s ease;
        opacity: 0;
    }

    .action-card:hover::before {
        opacity: 1;
        transform: rotate(45deg) translate(50%, 50%);
    }

    .action-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-color);
    }

    .action-icon {
        font-size: 3.5rem;
        margin-bottom: 20px;
    }

    .action-card h3 {
        margin: 0 0 15px 0;
        color: var(--gray-800);
        font-size: 1.4rem;
        font-weight: 600;
    }

    .action-card p {
        color: var(--gray-600);
        margin: 0 0 25px 0;
        line-height: 1.6;
    }

    /* Recent Activity */
    .recent-activity {
        margin-bottom: 50px;
    }

    .activity-list {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        margin-top: 30px;
    }

    .activity-item {
        display: flex;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid var(--gray-200);
        transition: background 0.2s ease;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item:hover {
        background: var(--gray-50);
    }

    .activity-icon {
        font-size: 1.5rem;
        margin-right: 15px;
        color: var(--primary-color);
    }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-weight: 600;
        margin-bottom: 5px;
    }

    .activity-title a {
        color: var(--gray-800);
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .activity-title a:hover {
        color: var(--primary-color);
    }

    .activity-meta {
        font-size: 0.9rem;
        color: var(--gray-500);
    }

    .activity-badge {
        background: var(--gray-100);
        color: var(--gray-600);
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .activity-footer {
        text-align: center;
        margin-top: 25px;
    }

    /* Getting Started */
    .getting-started {
        background: white;
        padding: 40px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        margin-bottom: 50px;
    }

    .steps-container {
        margin-top: 30px;
    }

    .step-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 30px;
        padding: 25px;
        background: var(--gray-50);
        border-radius: var(--radius-md);
        transition: all 0.3s ease;
    }

    .step-item:hover {
        background: var(--blue-50);
        transform: translateX(10px);
    }

    .step-number {
        background: var(--primary-color);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .step-content h3 {
        margin: 0 0 10px 0;
        color: var(--gray-800);
        font-size: 1.2rem;
    }

    .step-content p {
        margin: 0 0 15px 0;
        color: var(--gray-600);
        line-height: 1.6;
    }

    .step-code {
        background: var(--gray-800);
        color: var(--gray-100);
        padding: 12px 16px;
        border-radius: var(--radius-sm);
        font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, monospace;
        font-size: 0.9rem;
        display: block;
        overflow-x: auto;
    }

    .getting-started-footer {
        text-align: center;
        margin-top: 40px;
        padding-top: 30px;
        border-top: 1px solid var(--gray-200);
    }

    /* Last Activity */
    .last-activity {
        text-align: center;
        color: var(--gray-500);
        font-size: 0.9rem;
        margin-top: 40px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.2rem;
        }

        .hero-subtitle {
            font-size: 1.1rem;
        }

        .dashboard-stats {
            grid-template-columns: 1fr;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }

        .step-item {
            flex-direction: column;
            text-align: center;
        }

        .step-number {
            margin: 0 0 15px 0;
        }

        .step-content {
            width: 100%;
        }
    }
</style>