import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ArrowLeft, Calculator } from 'lucide-react';
import type { User } from '@/types';

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

interface Props {
  auth: { user: User };
  emissionFactors: EmissionFactor[];
  stakeholders: Stakeholder[];
}

export default function Create({ auth, emissionFactors, stakeholders }: Props) {
  const { data, setData, post, processing, errors } = useForm({
    emission_factor_id: '',
    stakeholder_id: '',
    measurement_date: new Date().toISOString().split('T')[0],
    activity_value: '',
    activity_unit: 'Liter',
    notes: '',
  });

  const [selectedFactor, setSelectedFactor] = useState<EmissionFactor | null>(null);
  const [emissionPreview, setEmissionPreview] = useState<number>(0);

  useEffect(() => {
    if (data.emission_factor_id) {
      const factor = emissionFactors.find((f) => f.id === Number(data.emission_factor_id));
      setSelectedFactor(factor || null);
    } else {
      setSelectedFactor(null);
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
    post('/scope-1');
  };

  const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(value);
  };

  return (
    <AppLayout>
      <Head title="Tambah Emisi Scope 1" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          <div className="flex items-center gap-4">
            <Link href="/scope-1">
              <Button variant="outline" size="icon">
                <ArrowLeft className="h-4 w-4" />
              </Button>
            </Link>
            <div>
              <h2 className="text-3xl font-bold tracking-tight">Tambah Emisi Scope 1</h2>
              <p className="text-muted-foreground mt-2">
                Input data emisi langsung dari sumber yang dimiliki atau dikendalikan organisasi
              </p>
            </div>
          </div>

          <form onSubmit={handleSubmit}>
            <Card>
              <CardHeader>
                <CardTitle>Formulir Input Emisi</CardTitle>
                <CardDescription>
                  Lengkapi formulir berikut untuk menambah data emisi Scope 1
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="space-y-2">
                  <Label htmlFor="emission_factor_id">
                    Faktor Emisi <span className="text-destructive">*</span>
                  </Label>
                  <Select
                    value={data.emission_factor_id}
                    onValueChange={(value) => setData('emission_factor_id', value)}
                  >
                    <SelectTrigger id="emission_factor_id">
                      <SelectValue placeholder="Pilih faktor emisi" />
                    </SelectTrigger>
                    <SelectContent>
                      {emissionFactors.map((factor) => (
                        <SelectItem key={factor.id} value={factor.id.toString()}>
                          {factor.name} ({formatNumber(factor.factor)} {factor.unit})
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.emission_factor_id && (
                    <p className="text-sm text-destructive">{errors.emission_factor_id}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="stakeholder_id">
                    Stakeholder <span className="text-destructive">*</span>
                  </Label>
                  <Select
                    value={data.stakeholder_id}
                    onValueChange={(value) => setData('stakeholder_id', value)}
                  >
                    <SelectTrigger id="stakeholder_id">
                      <SelectValue placeholder="Pilih stakeholder" />
                    </SelectTrigger>
                    <SelectContent>
                      {stakeholders.map((stakeholder) => (
                        <SelectItem key={stakeholder.id} value={stakeholder.id.toString()}>
                          {stakeholder.name} ({stakeholder.department})
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.stakeholder_id && (
                    <p className="text-sm text-destructive">{errors.stakeholder_id}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="measurement_date">
                    Tanggal Pengukuran <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    id="measurement_date"
                    type="date"
                    value={data.measurement_date}
                    onChange={(e) => setData('measurement_date', e.target.value)}
                  />
                  {errors.measurement_date && (
                    <p className="text-sm text-destructive">{errors.measurement_date}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="activity_value">
                    Nilai Aktivitas {selectedFactor && `(${selectedFactor.unit.split('/')[0]})`}{' '}
                    <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    id="activity_value"
                    type="number"
                    step="0.01"
                    placeholder="Masukkan nilai aktivitas"
                    value={data.activity_value}
                    onChange={(e) => setData('activity_value', e.target.value)}
                  />
                  {errors.activity_value && (
                    <p className="text-sm text-destructive">{errors.activity_value}</p>
                  )}
                </div>

                {selectedFactor && data.activity_value && (
                  <Alert>
                    <Calculator className="h-4 w-4" />
                    <AlertDescription>
                      <div className="space-y-2">
                        <div className="font-medium">Preview Perhitungan Emisi:</div>
                        <div className="text-sm space-y-1">
                          <div>
                            Faktor Emisi: {formatNumber(selectedFactor.factor)}{' '}
                            {selectedFactor.unit}
                          </div>
                          <div>
                            Nilai Aktivitas: {formatNumber(parseFloat(data.activity_value))}{' '}
                            {selectedFactor.unit.split('/')[0]}
                          </div>
                          <div className="text-lg font-bold text-primary pt-2">
                            Hasil Emisi: {formatNumber(emissionPreview)} kg CO₂eq
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
                    onChange={(e) => setData('notes', e.target.value)}
                    rows={4}
                  />
                  {errors.notes && <p className="text-sm text-destructive">{errors.notes}</p>}
                </div>

                <div className="flex items-center gap-4 pt-4">
                  <Button type="submit" disabled={processing}>
                    {processing ? 'Menyimpan...' : 'Simpan Data'}
                  </Button>
                  <Link href="/scope-1">
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
