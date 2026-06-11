import { NavLink, useNavigate } from 'react-router-dom';
import styles from './Sidebar.module.css';

const navItems = [
  { path: '/', label: 'Dashboard', icon: '📊' },
  { path: '/employees', label: 'Karyawan', icon: '👥' },
  { path: '/kpi-weights', label: 'Bobot KPI', icon: '⚖️' },
  { path: '/evaluations', label: 'Evaluasi', icon: '📋' },
  { path: '/reports', label: 'Laporan', icon: '📈' },
];

export default function Sidebar({ collapsed, setCollapsed }) {
  const navigate = useNavigate();

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    navigate('/login');
  };

  const user = (() => {
    try {
      return JSON.parse(localStorage.getItem('user')) || {};
    } catch {
      return {};
    }
  })();

  return (
    <aside className={`${styles.sidebar} ${collapsed ? styles.collapsed : ''}`}>
      <div className={styles.sidebarInner}>
        {/* Logo */}
        <div className={styles.logo} onClick={() => setCollapsed(!collapsed)}>
          <div className={styles.logoIcon}>
            <span>⚡</span>
          </div>
          {!collapsed && (
            <div className={styles.logoText}>
              <h1>KPI System</h1>
              <p>Web Manager</p>
            </div>
          )}
        </div>

        {/* Navigation */}
        <nav className={styles.nav}>
          {navItems.map((item) => (
            <NavLink
              key={item.path}
              to={item.path}
              end={item.path === '/'}
              className={({ isActive }) =>
                `${styles.navItem} ${isActive ? styles.active : ''}`
              }
            >
              <span className={styles.navIcon}>{item.icon}</span>
              {!collapsed && <span className={styles.navLabel}>{item.label}</span>}
            </NavLink>
          ))}
        </nav>

        {/* User Section */}
        <div className={styles.userSection}>
          {!collapsed && (
            <div className={styles.userInfo}>
              <div className={styles.userAvatar}>
                {(user.name || 'U').charAt(0).toUpperCase()}
              </div>
              <div className={styles.userDetails}>
                <p className={styles.userName}>{user.name || 'Manager'}</p>
                <p className={styles.userRole}>{user.role || 'Admin'}</p>
              </div>
            </div>
          )}
          <button className={styles.logoutBtn} onClick={handleLogout} title="Keluar">
            <span>🚪</span>
            {!collapsed && <span>Keluar</span>}
          </button>
        </div>
      </div>
    </aside>
  );
}
