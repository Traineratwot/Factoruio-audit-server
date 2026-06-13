import { Head } from '@inertiajs/react';
import { PrimeReactProvider } from 'primereact/api';
import 'primereact/resources/themes/lara-dark-cyan/theme.css';
import { Column } from 'primereact/column';
import { DataTable } from 'primereact/datatable';

export default function Welcome({ reports }: { reports: any[] }) {
    return (
        <>
            <Head title="Welcome" />
            <PrimeReactProvider>
                <DataTable value={reports} tableStyle={{ minWidth: '50rem' }}>
                    <Column field="mod_name" header="Name"></Column>
                    <Column field="mod_version" header="Version"></Column>
                    <Column field="score" header="Score"></Column>
                </DataTable>
            </PrimeReactProvider>
        </>
    );
}
