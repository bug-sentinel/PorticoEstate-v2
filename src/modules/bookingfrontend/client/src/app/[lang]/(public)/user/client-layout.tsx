'use client'
import {FC, PropsWithChildren, useMemo} from "react";
import {useBookingUser} from "@/service/hooks/api-hooks";
import {usePathname, useRouter} from "next/navigation";
import {useIsMobile} from "@/service/hooks/is-mobile";
import {useTrans} from "@/app/i18n/ClientTranslationProvider";
import {Spinner, Tabs} from "@digdir/designsystemet-react";
import styles from "@/components/layout/header/internal-nav/internal-nav.module.scss";
import Link from "next/link";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faArrowLeft} from "@fortawesome/free-solid-svg-icons";
import PageHeader from "@/components/page-header/page-header";
import {userSubPages} from "@/app/[lang]/(public)/user/user-page-helper";

interface UserLayoutProps extends PropsWithChildren {
}
const UserLayout: FC<UserLayoutProps> = (props) => {
    const user = useBookingUser();
    const pathname = usePathname();
    const router = useRouter();
    const path = pathname.split('/')
    const isMobile = useIsMobile();
    const t = useTrans();

    const currPath = '/' + (path.filter(a => a).slice(1).join('/'));

    const links = useMemo(() => {
        return userSubPages.filter(a => !a.needsDelegates || (user.data?.delegates?.length || 0) > 0);
    }, [user])


    // if (!user.data?.is_logged_in && !user.isLoading) {
    //     router.push('/')
    //     return;
    // }

    if (path.length === 3 && path[2] === 'user' && !isMobile) {
        router.push('/user' + links[0].relativePath)
        return;
    }

    if (user.isLoading) {
        return <Spinner title={'Loading user'}/>
    }


    return (
        <div>
            {/* Show tabs for desktop, links for mobile */}
            {isMobile && (<div className={`${styles.internalNavContainer} mx-3`}>
                <Link className={'link-text link-text-primary'} href={'/user'}>
                    <FontAwesomeIcon icon={faArrowLeft}/>
                    {t('common.back')}
                </Link>
            </div>)}

            {!isMobile && (
                <div>
                    <PageHeader title={t('bookingfrontend.my page')} className={'mb-2'}/>

                    <Tabs value={currPath}>
                        <Tabs.List>
                            {links.map((link) => {
                                const SVGIcon = link.icon;

                                return (
                                    <Link key={'/user' + link.relativePath} href={'/user' + link.relativePath} className={'link-text link-text-unset normal'}>
                                        <Tabs.Tab value={'/user' + link.relativePath} >
                                            <SVGIcon fontSize='1.75rem' aria-hidden/>
                                            {t(link.labelTag)}
                                        </Tabs.Tab>
                                    </Link>
                                )
                            })}
                        </Tabs.List>

                    </Tabs>
                    {/* Default to rendering details on /user route for desktop */}
                    {pathname === '/user' && <div>{props.children}</div>}
                </div>
            )}
            <div>{props.children}</div>
        </div>
    );
}


export default UserLayout