import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { User } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Calculator } from 'lucide-react';
import { useEffect, useState } from 'react';

interface EmissionFactor {
    id: number;
    name: string;
    scope: string;
    category: string;
    factor: number;
    unit: string;
}

interface Stakeholder {
    id: number;
    name: string;
    department: string;
}

interface Scope3Emission {
    id: number;
    emission_factor_id: number;
    stakeholder_id: number;
    measurement_date: string;
    category: string;
    activity_value: number;
    emission_result: number;
    notes?: string;
}

interface Props {
    auth: { user: User };
    emission: Scope3Emission;
    emissionFactors: EmissionFactor[];
    stakeholders: Stakeholder[];
}

const SCOPE3_CATEGORIES = [
    {
        value: 'upstream_transportation',
        label: 'Upstream Transportation & Distribution',
    },
    {
        value: 'downstream_transportation',
        label: 'Downstream Transportation & Distribution',
    },
    { value: 'waste_generated', label: 'Waste Generated in Operations' },
    { value: 'business_travel', label: 'Business Travel' },
    { value: 'employee_commuting', label: 'Employee Commuting' },
    { value: 'purchased_goods', label: 'Purchased Goods & Services' },
];

export default function Edit({
    auth,
    emission,
    emissionFactors,
    stakeholders,
}: Props) {
    const { data, setData, put, processing, errors } = useForm({
        emission_factor_id: emission.emission_factor_id.toString(),
        stakeholder_id: emission.stakeholder_id.toString(),
        measurement_date: emission.measurement_date
            ? new Date(emission.measurement_date).toISOString().substring(0, 10)
            : '',
        category: emission.category,
        activity_value: emission.activity_value.toString(),
        activity_unit: '',
        notes: emission.notes || '',
    });

    const [selectedFactor, setSelectedFactor] = useState<EmissionFactor | null>(
        null,
    );
    const [emissionPreview, setEmissionPreview] = useState<number>(0);

    useEffect(() => {
        if (data.emission_factor_id) {
            const factor = emissionFactors.find(
                (f) => f.id === Number(data.emission_factor_id),
            );
            setSelectedFactor(factor || null);
            if (factor) {
                // Extract activity unit from emission factor unit (e.g., "Liter" from "kg CO₂eq/Liter")
                const unit = factor.unit.split('/')[0] || '';
                setData('activity_unit', unit);
            }
        } else {
            setSelectedFactor(null);
            setData('activity_unit', '');
        }
    }, [data.emission_factor_id, emissionFactors]);

    useEffect(() => {
        if (selectedFactor && data.activity_value) {
            const activityValue = parseFloat(data.activity_value);
            if (!isNaN(activityValue)) {
                const result = (activityValue * selectedFactor.factor) / 1000;
                setEmissionPreview(result);
            } else {
                setEmissionPreview(0);
            }
        } else {
            setEmissionPreview(0);
        }
    }, [selectedFactor, data.activity_value]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/scope-3/${emission.id}`);
    };

    const formatNumber = (value: number) => {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value);
    };

    return (
        <AppLayout>
            <Head title="Edit Emisi Scope 3" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-4">
                        <Link href="/scope-3">
                            <Button variant="outline" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h2 className="text-3xl font-bold tracking-tight">
                                Edit Emisi Scope 3
                            </h2>
                            <p className="mt-2 text-muted-foreground">
                                Perbarui data emisi tidak langsung lainnya dari
                                aktivitas organisasi
                            </p>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit}>
                        <Card>
                            <CardHeader>
                                <CardTitle>Formulir Edit Emisi</CardTitle>
                                <CardDescription>
                                    Perbarui formulir berikut untuk mengubah
                                    data emisi Scope 3
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="category">
                                        Kategori{' '}
                                        <span className="text-destructive">
                                            *
                                        </span>
                                    </Label>
                                    <Select
                                        value={data.category}
                                        onValueChange={(value) =>
                                            setData('category', value)
                                        }
                                    >
                                        <SelectTrigger id="category">
                                            <SelectValue placeholder="Pilih kategori emisi" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {SCOPE3_CATEGORIES.map((cat) => (
                                                <SelectItem
                                                    key={cat.value}
                                                    value={cat.value}
                                                >
                                                    {cat.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.category && (
                                        <p className="text-sm text-destructive">
                                            {errors.category}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="emission_factor_id">
                                        Faktor Emisi{' '}
                                        <span className="text-destructive">
                                            *
                                        </span>
                                    </Label>
                                    <Select
                                        value={data.emission_factor_id}
                                        onValueChange={(value) =>
                                            setData('emission_factor_id', value)
                                        }
                                    >
                                        <SelectTrigger id="emission_factor_id">
                                            <SelectValue placeholder="Pilih faktor emisi" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {emissionFactors.map((factor) => (
                                                <SelectItem
                                                    key={factor.id}
                                                    value={factor.id.toString()}
                                                >
                                                    {factor.name} (
                                                    {formatNumber(
                                                        factor.factor,
                                                    )}{' '}
                                                    {factor.unit})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.emission_factor_id && (
                                        <p className="text-sm text-destructive">
                                            {errors.emission_factor_id}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="stakeholder_id">
                                        Stakeholder{' '}
                                        <span className="text-destructive">
                                            *
                                        </span>
                                    </Label>
                                    <Select
                                        value={data.stakeholder_id}
                                        onValueChange={(value) =>
                                            setData('stakeholder_id', value)
                                        }
                                    >
                                        <SelectTrigger id="stakeholder_id">
                                            <SelectValue placeholder="Pilih stakeholder" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {stakeholders.map((stakeholder) => (
                                                <SelectItem
                                                    key={stakeholder.id}
                                                    value={stakeholder.id.toString()}
                                                >
                                                    {stakeholder.name} (
                                                    {stakeholder.department})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.stakeholder_id && (
                                        <p className="text-sm text-destructive">
                                            {errors.stakeholder_id}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="measurement_date">
                                        Tanggal Pengukuran{' '}
                                        <span className="text-destructive">
                                            *
                                        </span>
                                    </Label>
                                    <Input
                                        id="measurement_date"
                                        type="date"
                                        value={data.measurement_date}
                                        onChange={(e) =>
                                            setData(
                                                'measurement_date',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    {errors.measurement_date && (
                                        <p className="text-sm text-destructive">
                                            {errors.measurement_date}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="activity_value">
                                        Nilai Aktivitas{' '}
                                        {selectedFactor &&
                                            `(${selectedFactor.unit.split('/')[0]})`}{' '}
                                        <span className="text-destructive">
                                            *
                                        </span>
                                    </Label>
                                    <Input
                                        id="activity_value"
                                        type="number"
                                        step="0.01"
                                        placeholder="Masukkan nilai aktivitas"
                                        value={data.activity_value}
                                        onChange={(e) =>
                                            setData(
                                                'activity_value',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    {errors.activity_value && (
                                        <p className="text-sm text-destructive">
                                            {errors.activity_value}
                                        </p>
                                    )}
                                </div>

                                {selectedFactor && data.activity_value && (
                                    <Alert>
                                        <Calculator className="h-4 w-4" />
                                        <AlertDescription>
                                            <div className="space-y-2">
                                                <div className="font-medium">
                                                    Preview Perhitungan Emisi:
                                                </div>
                                                <div className="space-y-1 text-sm">
                                                    <div>
                                                        Faktor Emisi:{' '}
                                                        {formatNumber(
                                                            selectedFactor.factor,
                                                        )}{' '}
                                                        {selectedFactor.unit}
                                                    </div>
                                                    <div>
                                                        Nilai Aktivitas:{' '}
                                                        {formatNumber(
                                                            parseFloat(
                                                                data.activity_value,
                                                            ),
                                                        )}{' '}
                                                        {
                                                            selectedFactor.unit.split(
                                                                '/',
                                                            )[0]
                                                        }
                                                    </div>
                                                    <div className="pt-2 text-lg font-bold text-primary">
                                                        Hasil Emisi:{' '}
                                                        {formatNumber(
                                                            emissionPreview,
                                                        )}{' '}
                                                        kg CO₂eq
                                                    </div>
                                                </div>
                                            </div>
                                        </AlertDescription>
                                    </Alert>
                                )}

                                <div className="space-y-2">
                                    <Label htmlFor="notes">Catatan</Label>
                                    <Textarea
                                        id="notes"
                                        placeholder="Tambahkan catatan tambahan (opsional)"
                                        value={data.notes}
                                        onChange={(e) =>
                                            setData('notes', e.target.value)
                                        }
                                        rows={4}
                                    />
                                    {errors.notes && (
                                        <p className="text-sm text-destructive">
                                            {errors.notes}
                                        </p>
                                    )}
                                </div>

                                <div className="flex items-center gap-4 pt-4">
                                    <Button type="submit" disabled={processing}>
                                        {processing
                                            ? 'Menyimpan...'
                                            : 'Perbarui Data'}
                                    </Button>
                                    <Link href="/scope-3">
                                        <Button type="button" variant="outline">
                                            Batal
                                        </Button>
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
