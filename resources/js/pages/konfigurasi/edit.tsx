import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Konfigurasi Faktor Emisi', href: '/konfigurasi' },
    { title: 'Edit Faktor Emisi', href: '#' },
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

interface Props {
    factor: EmissionFactor;
}

export default function KonfigurasiEdit({ factor }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: factor.name || '',
        scope: factor.scope || 'scope_1',
        category: factor.category || '',
        factor: factor.factor || '',
        unit: factor.unit || '',
        description: factor.description || '',
        source: factor.source || '',
        is_active: factor.is_active,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/konfigurasi/${factor.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Faktor Emisi" />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Link href="/konfigurasi">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Kembali
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Edit Faktor Emisi</h1>
                        <p className="text-muted-foreground">
                            Update informasi faktor emisi
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Informasi Faktor Emisi</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Nama Faktor Emisi *</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="e.g., Solar (Diesel)"
                                        required
                                    />
                                    {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="scope">Scope *</Label>
                                    <Select value={data.scope} onValueChange={(value) => setData('scope', value)}>
                                        <SelectTrigger id="scope">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="scope_1">Scope 1 - Emisi Langsung</SelectItem>
                                            <SelectItem value="scope_2">Scope 2 - Emisi Energi Tidak Langsung</SelectItem>
                                            <SelectItem value="scope_3">Scope 3 - Emisi Rantai Pasok</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.scope && <p className="text-sm text-red-500">{errors.scope}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="category">Kategori</Label>
                                    <Input
                                        id="category"
                                        value={data.category}
                                        onChange={(e) => setData('category', e.target.value)}
                                        placeholder="e.g., Stationary Combustion"
                                    />
                                    {errors.category && <p className="text-sm text-red-500">{errors.category}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="factor">Faktor Emisi *</Label>
                                    <Input
                                        id="factor"
                                        type="number"
                                        step="0.0001"
                                        value={data.factor}
                                        onChange={(e) => setData('factor', e.target.value)}
                                        placeholder="e.g., 2.68"
                                        required
                                    />
                                    {errors.factor && <p className="text-sm text-red-500">{errors.factor}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="unit">Unit *</Label>
                                    <Input
                                        id="unit"
                                        value={data.unit}
                                        onChange={(e) => setData('unit', e.target.value)}
                                        placeholder="e.g., kg CO2eq/Liter"
                                        required
                                    />
                                    {errors.unit && <p className="text-sm text-red-500">{errors.unit}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="source">Sumber</Label>
                                    <Input
                                        id="source"
                                        value={data.source}
                                        onChange={(e) => setData('source', e.target.value)}
                                        placeholder="e.g., GHG Protocol 2004"
                                    />
                                    {errors.source && <p className="text-sm text-red-500">{errors.source}</p>}
                                </div>

                                <div className="space-y-2 md:col-span-2">
                                    <Label htmlFor="description">Deskripsi</Label>
                                    <Input
                                        id="description"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        placeholder="Deskripsi singkat tentang faktor emisi ini"
                                    />
                                    {errors.description && <p className="text-sm text-red-500">{errors.description}</p>}
                                </div>

                                <div className="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        id="is_active"
                                        checked={data.is_active}
                                        onChange={(e) => setData('is_active', e.target.checked)}
                                        className="h-4 w-4"
                                    />
                                    <Label htmlFor="is_active" className="cursor-pointer">
                                        Aktif
                                    </Label>
                                </div>
                            </div>

                            <div className="flex justify-end gap-4">
                                <Link href="/konfigurasi">
                                    <Button type="button" variant="outline">
                                        Batal
                                    </Button>
                                </Link>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Menyimpan...' : 'Update'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
