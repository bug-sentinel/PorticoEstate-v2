'use client'
import {FC} from 'react';
import {useTrans} from "@/app/i18n/ClientTranslationProvider";
import PageHeader from "@/components/page-header/page-header";

interface InvoicesProps {
}

const Invoices: FC<InvoicesProps> = (props) => {
    const t = useTrans();
    return (
        <main>
            {/*<PageHeader title={t('bookingfrontend.my page')}/>*/}
        </main>
    );
}

export default Invoices

