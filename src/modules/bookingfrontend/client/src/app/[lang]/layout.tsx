import {dir} from 'i18next'
import {languages, ILanguage} from "@/app/i18n/settings";

import type {Metadata} from "next";
import {Roboto} from "next/font/google";
import '@digdir/designsystemet-css';
import '@digdir/designsystemet-theme';
import "@/app/globals.scss";
import {FC, PropsWithChildren} from "react";

export async function generateStaticParams() {
    return languages.map((lng) => ({lng}))
}


const inter = Roboto({weight: ['100', '300', '400', '500', '700', '900'], subsets: ['latin']});

export const revalidate = 120;

export const metadata: Metadata = {
    title: "Create Next App",
    description: "Generated by create next app",
};


interface RootLayoutProps extends PropsWithChildren {
    params: {
        lang: string;
    }

}

const RootLayout: FC<RootLayoutProps> = (props) => {

    return (
        <html lang={props.params.lang} dir={dir(props.params.lang)}>
        <body className={inter.className}>
        <div className={'container-xxl container-fluid'}>
            {props.children}
        </div>
        </body>
        </html>
    );
}
export default RootLayout