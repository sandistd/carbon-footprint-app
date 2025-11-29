import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Pencil, Trash2, Plus, Search } from 'lucide-react';
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

interface Scope3Emission {
  id: number;
  emission_factor: EmissionFactor;
  stakeholder: Stakeholder;
  measurement_date: string;
  category: string;
  activity_value: number;
  emission_result: number;
  notes?: string;
  created_at: string;
}

interface Props {
  auth: { user: User };
  emissions: {
    data: Scope3Emission[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  emissionFactors: EmissionFactor[];
  departments: string[];
  filters?: {
    emission_factor_id?: number;
    department?: string;
    category?: string;
    date_from?: string;
    date_to?: string;
  };
}

const SCOPE3_CATEGORIES = [
  { value: 'upstream_transportation', label: 'Upstream Transportation & Distribution' },
  { value: 'downstream_transportation', label: 'Downstream Transportation & Distribution' },
  { value: 'waste_generated', label: 'Waste Generated in Operations' },
  { value: 'business_travel', label: 'Business Travel' },
  { value: 'employee_commuting', label: 'Employee Commuting' },
  { value: 'purchased_goods', label: 'Purchased Goods & Services' },
];

export default function Index({
  auth,
  emissions,
  emissionFactors,
  departments,
  filters = {},
}: Props) {
  const [emissionFactorId, setEmissionFactorId] = useState(
    filters.emission_factor_id?.toString() || 'all'
  );
  const [department, setDepartment] = useState(
    filters.department || 'all'
  );
  const [category, setCategory] = useState(filters.category || 'all');
  const [dateFrom, setDateFrom] = useState(filters.date_from || '');
  const [dateTo, setDateTo] = useState(filters.date_to || '');

  const handleFilter = () => {
    router.get(
      '/scope-3',
      {
        emission_factor_id: emissionFactorId !== 'all' ? emissionFactorId : undefined,
        department: department !== 'all' ? department : undefined,
        category: category !== 'all' ? category : undefined,
        date_from: dateFrom || undefined,
        date_to: dateTo || undefined,
      },
      {
        preserveState: true,
        preserveScroll: true,
      }
    );
  };

  const handleDelete = (id: number) => {
    if (confirm('Yakin ingin menghapus data ini?')) {
      router.delete(`/scope-3/${id}`, {
        preserveScroll: true,
      });
    }
  };

  const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(value);
  };

  const getCategoryLabel = (categoryValue: string) => {
    const cat = SCOPE3_CATEGORIES.find((c) => c.value === categoryValue);
    return cat?.label || categoryValue;
  };

  return (
    <AppLayout>
      <Head title="Scope 3 - Emisi Tidak Langsung Lainnya" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-3xl font-bold tracking-tight">
                Scope 3 - Emisi Tidak Langsung Lainnya
              </h2>
              <p className="text-muted-foreground mt-2">
                Emisi tidak langsung lainnya dari aktivitas organisasi (distribusi, limbah,
                perjalanan)
              </p>
            </div>
            <Link href="/scope-3/create">
              <Button>
                <Plus className="mr-2 h-4 w-4" />
                Tambah Data
              </Button>
            </Link>
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Filter Data</CardTitle>
              <CardDescription>
                Filter data emisi berdasarkan faktor emisi, stakeholder, kategori, dan rentang
                tanggal
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div className="space-y-2">
                  <label className="text-sm font-medium">Faktor Emisi</label>
                  <Select value={emissionFactorId} onValueChange={setEmissionFactorId}>
                    <SelectTrigger>
                      <SelectValue placeholder="Semua Faktor Emisi" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">Semua Faktor Emisi</SelectItem>
                      {emissionFactors.map((factor) => (
                        <SelectItem key={factor.id} value={factor.id.toString()}>
                          {factor.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium">Departemen</label>
                  <Select value={department} onValueChange={setDepartment}>
                    <SelectTrigger>
                      <SelectValue placeholder="Semua Departemen" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">Semua Departemen</SelectItem>
                      {departments.map((dept) => (
                        <SelectItem key={dept} value={dept}>
                          {dept}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium">Kategori</label>
                  <Select value={category} onValueChange={setCategory}>
                    <SelectTrigger>
                      <SelectValue placeholder="Semua Kategori" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">Semua Kategori</SelectItem>
                      {SCOPE3_CATEGORIES.map((cat) => (
                        <SelectItem key={cat.value} value={cat.value}>
                          {cat.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium">Tanggal Dari</label>
                  <Input
                    type="date"
                    value={dateFrom}
                    onChange={(e) => setDateFrom(e.target.value)}
                  />
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium">Tanggal Sampai</label>
                  <Input
                    type="date"
                    value={dateTo}
                    onChange={(e) => setDateTo(e.target.value)}
                  />
                </div>

                <div className="md:col-span-5">
                  <Button onClick={handleFilter} className="w-full md:w-auto">
                    <Search className="mr-2 h-4 w-4" />
                    Terapkan Filter
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Daftar Emisi Scope 3</CardTitle>
              <CardDescription>
                Total {emissions.total} data emisi tidak langsung lainnya
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Tanggal</TableHead>
                    <TableHead>Kategori</TableHead>
                    <TableHead>Faktor Emisi</TableHead>
                    <TableHead>Stakeholder</TableHead>
                    <TableHead className="text-right">Nilai Aktivitas</TableHead>
                    <TableHead className="text-right">Hasil Emisi</TableHead>
                    <TableHead>Catatan</TableHead>
                    <TableHead className="text-right">Aksi</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {emissions.data.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={8} className="text-center text-muted-foreground">
                        Belum ada data emisi
                      </TableCell>
                    </TableRow>
                  ) : (
                    emissions.data.map((emission) => (
                      <TableRow key={emission.id}>
                        <TableCell>
                          {new Date(emission.measurement_date).toLocaleDateString('id-ID', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                          })}
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline">{getCategoryLabel(emission.category)}</Badge>
                        </TableCell>
                        <TableCell>
                          <div className="space-y-1">
                            <div className="font-medium">{emission.emission_factor.name}</div>
                            <Badge variant="outline" className="text-xs">
                              {formatNumber(emission.emission_factor.factor)}{' '}
                              {emission.emission_factor.unit}
                            </Badge>
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="space-y-1">
                            <div className="font-medium">{emission.stakeholder.name}</div>
                            <div className="text-xs text-muted-foreground">
                              {emission.stakeholder.department}
                            </div>
                          </div>
                        </TableCell>
                        <TableCell className="text-right">
                          {formatNumber(emission.activity_value)}{' '}
                          {emission.emission_factor.unit.split('/')[0]}
                        </TableCell>
                        <TableCell className="text-right">
                          <Badge variant="secondary">
                            {formatNumber(emission.emission_result)} kg CO₂eq
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <div className="max-w-xs truncate text-sm text-muted-foreground">
                            {emission.notes || '-'}
                          </div>
                        </TableCell>
                        <TableCell className="text-right">
                          <div className="flex items-center justify-end gap-2">
                            <Link href={`/scope-3/${emission.id}/edit`}>
                              <Button variant="outline" size="icon">
                                <Pencil className="h-4 w-4" />
                              </Button>
                            </Link>
                            <Button
                              variant="outline"
                              size="icon"
                              onClick={() => handleDelete(emission.id)}
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </div>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>

              {emissions.last_page > 1 && (
                <div className="flex items-center justify-between mt-4">
                  <div className="text-sm text-muted-foreground">
                    Halaman {emissions.current_page} dari {emissions.last_page}
                  </div>
                  <div className="flex gap-2">
                    {emissions.current_page > 1 && (
                      <Link
                        href={`/scope-3?page=${emissions.current_page - 1}` +
                          (emissionFactorId !== 'all' ? `&emission_factor_id=${emissionFactorId}` : '') +
                          (department !== 'all' ? `&department=${department}` : '') +
                          (category !== 'all' ? `&category=${category}` : '') +
                          (dateFrom ? `&date_from=${dateFrom}` : '') +
                          (dateTo ? `&date_to=${dateTo}` : '')}
                        preserveState
                        preserveScroll
                      >
                        <Button variant="outline">Sebelumnya</Button>
                      </Link>
                    )}
                    {emissions.current_page < emissions.last_page && (
                      <Link
                        href={`/scope-3?page=${emissions.current_page + 1}` +
                          (emissionFactorId !== 'all' ? `&emission_factor_id=${emissionFactorId}` : '') +
                          (department !== 'all' ? `&department=${department}` : '') +
                          (category !== 'all' ? `&category=${category}` : '') +
                          (dateFrom ? `&date_from=${dateFrom}` : '') +
                          (dateTo ? `&date_to=${dateTo}` : '')}
                        preserveState
                        preserveScroll
                      >
                        <Button variant="outline">Selanjutnya</Button>
                      </Link>
                    )}
                  </div>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}
