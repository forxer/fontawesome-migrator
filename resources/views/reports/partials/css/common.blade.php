<style>
    /* Variables CSS unifiées - Design System FontAwesome Migrator */
    :root {
        --primary-color: #4299e1;
        --primary-hover: #3182ce;
        --primary-dark: #3182ce;
        --secondary-color: #667eea;
        --success-color: #48bb78;
        --error-color: #e53e3e;
        --warning-color: #ed8936;
        --danger-color: #e53e3e;
        --danger-dark: #dc2626;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --blue-50: #eff6ff;
        --blue-100: #dbeafe;
        --blue-500: #3b82f6;
        --blue-600: #2563eb;
        
        /* Spacing system */
        --spacing-xs: 4px;
        --spacing-sm: 8px;
        --spacing-md: 16px;
        --spacing-lg: 24px;
        --spacing-xl: 32px;
        --spacing-2xl: 48px;
        
        /* Border radius */
        --radius-sm: 6px;
        --radius-md: 8px;
        --radius-lg: 12px;
        --radius-xl: 16px;
        
        /* Shadows */
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.1);
        --shadow-lg: 0 8px 32px rgba(0,0,0,0.15);
        --shadow-colored: 0 8px 32px rgba(66, 153, 225, 0.3);
    }

    * {
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        color: var(--gray-800);
        line-height: 1.6;
    }

    /* Navigation */
    .navbar {
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .navbar-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 60px;
    }

    .navbar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        font-size: 1.2em;
        color: var(--gray-800);
    }

    .navbar-logo {
        font-size: 1.5em;
    }

    .navbar-menu {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        gap: 8px;
    }

    .navbar-item {
        margin: 0;
    }

    .navbar-link {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        color: var(--gray-600);
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s;
        font-weight: 500;
    }

    .navbar-link:hover {
        background: var(--gray-100);
        color: var(--gray-800);
    }

    .navbar-link.active {
        background: var(--primary-color);
        color: white;
    }

    /* Fil d'ariane */
    .breadcrumb {
        background: white;
        border-bottom: 1px solid var(--gray-200);
        padding: 12px 0;
    }

    .breadcrumb-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .breadcrumb-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9em;
    }

    .breadcrumb-item {
        margin: 0;
    }

    .breadcrumb-separator {
        color: var(--gray-400);
        margin: 0;
    }

    .breadcrumb-link {
        color: var(--primary-color);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: color 0.2s;
    }

    .breadcrumb-link:hover {
        color: var(--primary-hover);
        text-decoration: underline;
    }

    .breadcrumb-current {
        color: var(--gray-600);
        font-weight: 500;
    }

    /* Container */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .container.with-breadcrumb {
        padding-top: 20px;
    }

    .header {
        background: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        text-align: center;
    }

    .header h1 {
        margin: 0 0 10px 0;
        color: var(--gray-800);
        font-size: 2.5em;
        font-weight: 700;
    }

    .header p {
        color: var(--gray-500);
        margin: 0;
        font-size: 1.1em;
    }

    .section {
        background: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }

    .section h2 {
        margin: 0 0 20px 0;
        color: var(--gray-800);
        font-size: 1.5em;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        text-align: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    }

    .stat-number {
        font-size: 2.5em;
        font-weight: bold;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .stat-label {
        color: var(--gray-500);
        font-size: 0.9em;
        font-weight: 500;
    }

    .btn {
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        font-size: 0.95em;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
    }

    .btn-danger {
        background: var(--danger-color);
        color: white;
    }

    .btn-danger:hover {
        background: var(--danger-dark);
    }

    .btn-sm {
        padding: 8px 15px;
        font-size: 0.9em;
    }

    /* Tables */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    th, td {
        text-align: left;
        padding: 12px 15px;
        border-bottom: 1px solid var(--gray-200);
    }

    th {
        background: var(--gray-50);
        font-weight: 600;
        color: var(--gray-700);
        font-size: 0.9em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover {
        background: var(--gray-50);
    }

    /* Alerts et messages */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .alert-info {
        background: var(--blue-50);
        color: #1e40af;
        border: 1px solid #93c5fd;
    }

    /* Éléments de contenu */
    .file-item {
        border-bottom: 1px solid var(--gray-200);
        padding: 20px 0;
    }

    .file-item:last-child {
        border-bottom: none;
    }

    .file-path {
        font-weight: 600;
        color: var(--gray-800);
        font-size: 1.1em;
        margin-bottom: 10px;
    }

    .change-item {
        margin: 8px 0;
        padding: 12px;
        background: var(--gray-50);
        border-radius: 6px;
        font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, monospace;
        font-size: 0.9em;
        line-height: 1.4;
    }

    .change-from {
        color: var(--danger-color);
        font-weight: 500;
    }

    .change-to {
        color: var(--success-color);
        font-weight: 500;
    }

    /* Badge styles */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8em;
        font-weight: 500;
        gap: 4px;
    }

    .badge-icon {
        color: var(--primary-color);
    }

    .badge-asset {
        color: #7c3aed;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-error {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Responsive */
    @media (max-width: 768px) {
        body {
            padding: 15px;
        }

        .header h1 {
            font-size: 2em;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .section {
            padding: 20px;
        }

        table {
            font-size: 0.9em;
        }

        th, td {
            padding: 10px;
        }
    }

    /* Animations */
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Système de boutons unifié */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--radius-md);
        text-decoration: none;
        font-size: 0.9em;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-transform: none;
        white-space: nowrap;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn:hover::before {
        left: 100%;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 2px 8px rgba(66, 153, 225, 0.3);
        border: 1px solid var(--primary-color);
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(66, 153, 225, 0.4);
        border-color: var(--primary-hover);
    }

    .btn-secondary {
        background: var(--gray-500);
        color: white;
        box-shadow: 0 2px 8px rgba(107, 114, 128, 0.3);
        border: 1px solid var(--gray-500);
    }

    .btn-secondary:hover {
        background: var(--gray-600);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
        border-color: var(--gray-600);
    }

    .btn-warning {
        background: var(--warning-color);
        color: white;
        box-shadow: 0 2px 8px rgba(237, 137, 54, 0.3);
        border: 1px solid var(--warning-color);
    }

    .btn-warning:hover {
        background: #dd7f2b;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(237, 137, 54, 0.4);
        border-color: #dd7f2b;
    }

    /* Bouton retour en haut unifié */
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 16px rgba(66, 153, 225, 0.3);
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1001;
    }

    .back-to-top.visible,
    .back-to-top.show {
        opacity: 1;
        visibility: visible;
    }

    .back-to-top:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(66, 153, 225, 0.4);
    }

    /* Collapsible content */
    .collapsible-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }

    .collapsible-content.active {
        max-height: 2000px !important;
        overflow: visible !important;
        transition: max-height 0.3s ease-in;
    }

    /* Surlignage de recherche */
    .highlight-match {
        background: #fef3c7;
        color: #92400e;
        padding: 2px 4px;
        border-radius: 3px;
    }

    /* Enhanced sections */
    .enhanced-section {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-lg);
        padding: var(--spacing-lg);
    }

    /* Charts and visualizations */
    .chart-container {
        position: relative;
        height: 300px;
        margin: var(--spacing-lg) 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: white;
        border-radius: var(--radius-md);
        padding: var(--spacing-lg);
    }

    /* Modal styles communes */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-content {
        background: white;
        padding: var(--spacing-xl);
        border-radius: var(--radius-lg);
        max-width: 80%;
        max-height: 80%;
        overflow-y: auto;
        position: relative;
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--gray-200);
        padding-bottom: var(--spacing-md);
    }

    .modal-header h3 {
        margin: 0;
        color: var(--gray-800);
        font-size: 1.25rem;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--gray-400);
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
    }

    .modal-close:hover {
        color: var(--gray-600);
        background: var(--gray-100);
    }

    .modal-body {
        color: var(--gray-700);
    }

    .modal-body pre {
        background: var(--gray-50);
        padding: var(--spacing-md);
        border-radius: var(--radius-sm);
        overflow-x: auto;
        font-size: 0.9rem;
        border: 1px solid var(--gray-200);
    }

    .modal-body ul {
        padding-left: var(--spacing-lg);
        margin: var(--spacing-md) 0;
    }

    .modal-body li {
        margin: var(--spacing-sm) 0;
    }

    /* Classes communes partagées par les vues reports et sessions */
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
        gap: 25px;
    }

    .report-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary-color);
    }

    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }

    .report-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .report-icon {
        font-size: 2.5em;
        color: var(--primary-color);
    }

    .report-title {
        flex: 1;
    }

    .report-title h3 {
        margin: 0 0 5px 0;
        color: var(--gray-800);
        font-size: 1.3em;
        font-weight: 600;
    }

    .report-title h3 span[data-tooltip] {
        text-decoration: underline;
        text-decoration-style: dotted;
        text-underline-offset: 2px;
        color: var(--primary-color);
    }

    .report-date {
        color: var(--gray-500);
        font-size: 0.95em;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .report-meta {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 15px;
        margin-bottom: 25px;
        padding: 20px;
        background: linear-gradient(135deg, var(--gray-50) 0%, #ffffff 100%);
        border-radius: 10px;
        border: 1px solid var(--gray-200);
    }

    .meta-item {
        text-align: center;
    }

    .meta-value {
        font-size: 1.4em;
        font-weight: bold;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .meta-value[title] {
        cursor: help;
        text-decoration: underline;
        text-decoration-style: dotted;
        text-underline-offset: 2px;
        position: relative;
    }

    /* Tooltip simple avec CSS */
    [data-tooltip] {
        position: relative;
        cursor: help;
    }

    [data-tooltip]:hover::before {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: var(--gray-800);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85em;
        white-space: nowrap;
        z-index: 100;
        margin-bottom: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        font-weight: normal;
    }

    [data-tooltip]:hover::after {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 6px solid transparent;
        border-top-color: var(--gray-800);
        margin-bottom: -4px;
        z-index: 100;
    }

    .meta-label {
        color: var(--gray-500);
        font-size: 0.85em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .report-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* Section title avec effet de ligne */
    .section-title {
        margin: 0 0 var(--spacing-lg) 0;
        color: var(--gray-800);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: 1.5rem;
        font-weight: 600;
    }

    .section-title::after {
        content: '';
        flex: 1;
        height: 2px;
        background: linear-gradient(90deg, var(--primary-color), transparent);
        margin-left: var(--spacing-md);
    }

    /* Empty state */
    .empty-state {
        background: white;
        padding: 80px 40px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        border: 2px dashed var(--gray-300);
    }

    .empty-icon {
        font-size: 5em;
        color: var(--gray-300);
        margin-bottom: 25px;
    }

    .empty-title {
        font-size: 1.6em;
        color: var(--gray-800);
        margin-bottom: 15px;
        font-weight: 600;
    }

    .empty-description {
        color: var(--gray-500);
        margin-bottom: 30px;
        line-height: 1.6;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .empty-code {
        background: var(--gray-50);
        padding: 15px 20px;
        border-radius: 8px;
        color: var(--gray-800);
        font-family: 'Courier New', monospace;
        border: 1px solid var(--gray-200);
        display: inline-block;
    }

    /* Actions communes */
    .actions {
        background: white;
        padding: var(--spacing-lg);
        border-radius: var(--radius-lg);
        margin-bottom: var(--spacing-xl);
        box-shadow: var(--shadow-md);
        display: flex;
        gap: var(--spacing-md);
        align-items: center;
        flex-wrap: wrap;
        border-left: 4px solid var(--primary-color);
        border: 1px solid var(--gray-200);
        transition: all 0.3s ease;
    }

    .actions:hover {
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-color);
    }

    /* Stats summary communes */
    .stats-summary {
        background: white;
        padding: var(--spacing-lg);
        border-radius: var(--radius-lg);
        margin-bottom: var(--spacing-xl);
        box-shadow: var(--shadow-md);
        border-left: 4px solid var(--success-color);
        border: 1px solid var(--gray-200);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stats-summary::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--success-color), var(--primary-color));
    }

    .stats-summary:hover {
        box-shadow: var(--shadow-lg);
        border-color: var(--success-color);
    }

    .stat-item {
        text-align: center;
        padding: 15px;
        background: var(--gray-50);
        border-radius: var(--radius-md);
    }

    .stat-number {
        font-size: 2.5em;
        font-weight: bold;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .stat-label {
        color: var(--gray-500);
        font-size: 0.9em;
        font-weight: 500;
    }

    @yield('additional-styles')
</style>
