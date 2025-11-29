import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Konfigurasi Faktor Emisi',
        href: '/konfigurasi',
    },
];

interface EmissionFactor {
    id: number;
    name: string;
    scope: string;
    category: string | null;
    factor: string;
    unit: string;
    description: string | null;
    source: string | null;
    is_active: boolean;
}

interface PaginatedData {
    data: EmissionFactor[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
}

interface Props {
    factors: PaginatedData;
    filters: {
        scope?: string;
    };
}

export default function KonfigurasiIndex({ factors, filters }: Props) {
    const [scope, setScope] = useState(filters.scope || 'all');

    const handleFilter = () => {
        router.get('/konfigurasi', {
            scope: scope !== 'all' ? scope : undefined,
        }, {
            preserveState: true,
        });
    };

    const handleDelete = (id: number) => {
        if (confirm('Apakah Anda yakin ingin menghapus faktor emisi ini?')) {
            router.delete(`/konfigurasi/${id}`);
        }
    };

    const getScopeLabel = (scope: string) => {
        const labels: Record<string, string> = {
            scope_1: 'Scope 1',
            scope_2: 'Scope 2',
            scope_3: 'Scope 3',
        };
        return labels[scope] || scope;
    };

    const getScopeBadgeColor = (scope: string) => {
        const colors: Record<string, string> = {
            scope_1: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            scope_2: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            scope_3: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        };
        return colors[scope] || '';
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Konfigurasi Faktor Emisi" />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Konfigurasi Faktor Emisi</h1>
                        <p className="text-muted-foreground">
                            Kelola faktor emisi untuk perhitungan emisi karbon Scope 1, 2, dan 3
                        </p>
                    </div>
                    <Link href="/konfigurasi/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Tambah Faktor Emisi
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filter</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex gap-4">
                            <div className="w-64">
                                <Select value={scope} onValueChange={setScope}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih Scope" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua Scope</SelectItem>
                                        <SelectItem value="scope_1">Scope 1</SelectItem>
                                        <SelectItem value="scope_2">Scope 2</SelectItem>
                                        <SelectItem value="scope_3">Scope 3</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button onClick={handleFilter}>Filter</Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama</TableHead>
                                    <TableHead>Scope</TableHead>
                                    <TableHead>Kategori</TableHead>
                                    <TableHead className="text-right">Faktor Emisi</TableHead>
                                    <TableHead>Unit</TableHead>
                                    <TableHead>Sumber</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {factors.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={8} className="text-center text-muted-foreground">
                                            Tidak ada data faktor emisi
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    factors.data.map((factor) => (
                                        <TableRow key={factor.id}>
                                            <TableCell className="font-medium">{factor.name}</TableCell>
                                            <TableCell>
                                                <Badge className={getScopeBadgeColor(factor.scope)}>
                                                    {getScopeLabel(factor.scope)}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-sm text-muted-foreground">
                                                {factor.category || '-'}
                                            </TableCell>
                                            <TableCell className="text-right font-mono">
                                                {parseFloat(factor.factor).toFixed(2)}
                                            </TableCell>
                                            <TableCell className="text-sm">{factor.unit}</TableCell>
                                            <TableCell className="text-sm text-muted-foreground">
                                                {factor.source || '-'}
                                            </TableCell>
                                            <TableCell>
                                                {factor.is_active ? (
                                                    <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        Aktif
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="outline">Nonaktif</Badge>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Link href={`/konfigurasi/${factor.id}/edit`}>
                                                        <Button variant="outline" size="sm">
                                                            <Pencil className="h-4 w-4" />
                                                        </Button>
                                                    </Link>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => handleDelete(factor.id)}
                                                    >
                                                        <Trash2 className="h-4 w-4 text-red-500" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {/* Pagination */}
                {factors.last_page > 1 && (
                    <div className="flex justify-center gap-2">
                        {factors.links.map((link, index) => (
                            <Button
                                key={index}
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                disabled={!link.url}
                                onClick={() => link.url && router.get(link.url)}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
