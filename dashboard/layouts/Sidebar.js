import React from 'react';
import styles from '../styles/Sidebar.module.scss'

const Sidebar = () => {
    return (<aside className={`${styles.aside} `}>
        <div className={"row"}>
            <div className={`${styles.logoWrap} d-flex a-i-center`}>
                <div className={styles.logo}>
                    <img src={"images/logo.png"} alt={"logo"}/>
                </div>
            </div>
            <ul className={`${styles.mainMenu} items`}>
                <li className={ `${styles.item} ${styles.hasIcon}`}>
                    <div>
                        <svg id="dashboard" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40">
                            <path d="M39.3,15.74v8.73H14.79V15.74Z"/>
                            <path d="M.05,9.43V.72H24.57V9.43Z"/>
                            <path d="M24.57,30.77V39.5H.06V30.77Z"/>
                            <path d="M8.68,24.47H.05V15.73H8.68Z"/>
                            <path d="M39.29,9.44H30.66V.71h8.63Z"/>
                            <path d="M39.3,39.5H30.67V30.78H39.3Z"/>
                        </svg>
                    </div>
                    <span>
                    dashboard
                </span>
                </li>
            </ul>
        </div>
    </aside>);
}
export default Sidebar;
