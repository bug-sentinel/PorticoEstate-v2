'use client'
import {FC, useState, useEffect, PropsWithChildren} from 'react';
import { Button } from "@digdir/designsystemet-react";
import styles from './header-menu-content.module.scss'

interface HeaderMenuContentProps extends PropsWithChildren{}

const HeaderMenuContent: FC<HeaderMenuContentProps> = (props) => {
    const [drawerOpen, setDrawerOpen] = useState<boolean>(false);

    useEffect(() => {
        if (drawerOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'unset';
        }

        return () => {
            document.body.style.overflow = 'unset';
        };
    }, [drawerOpen]);

    const toggleDrawer = () => {
        setDrawerOpen(!drawerOpen);
    };

    return (
        <div>
            <button onClick={toggleDrawer} className={`${styles.toggleMenuBtn}`} >
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div className={`${styles.overlay} ${drawerOpen ? styles.active : ''}`} onClick={toggleDrawer}></div>
            <div id="mySidenav" className={`${styles.sidenav} ${drawerOpen ? styles.open : ''}`}>
                <Button variant={"tertiary"} className={styles.closebtn} onClick={toggleDrawer}>&times;</Button>
                {props.children}
                {/*<a href="#">About</a>*/}
                {/*<a href="#">Services</a>*/}
                {/*<a href="#">Clients</a>*/}
                {/*<a href="#">Contact</a>*/}
            </div>
        </div>
    );
}

export default HeaderMenuContent