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
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Pencil, Trash2, Plus, Search, Users } from 'lucide-react';
import type { User } from '@/types';

interface Stakeholder {
  id: number;
  name: string;
  email: string;
  position?: string;
  department?: string;
  receive_alerts: boolean;
  created_at: string;
}

interface Props {
  auth: { user: User };
  stakeholders: {
    data: Stakeholder[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  filters?: {
    search?: string;
  };
}

export default function Index({ auth, stakeholders, filters = {} }: Props) {
  const [search, setSearch] = useState(filters.search || '');

  const handleSearch = () => {
    router.get(
      '/stakeholders',
      { search: search || undefined },
      {
        preserveState: true,
        preserveScroll: true,
      }
    );
  };

  const handleDelete = (id: number, name: string) => {
    if (confirm(`Yakin ingin menghapus stakeholder "${name}"?`)) {
      router.delete(`/stakeholders/${id}`, {
        preserveScroll: true,
      });
    }
  };

  return (
    <AppLayout>
      <Head title="Stakeholders" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          <div className="flex items-center justify-between">
            <div>
              <div className="flex items-center gap-3">
                <Users className="h-8 w-8 text-primary" />
                <h2 className="text-3xl font-bold tracking-tight">Stakeholders</h2>
              </div>
              <p className="text-muted-foreground mt-2">
                Kelola data stakeholder yang terlibat dalam pelaporan emisi karbon
              </p>
            </div>
            <Link href="/stakeholders/create">
              <Button>
                <Plus className="mr-2 h-4 w-4" />
                Tambah Stakeholder
              </Button>
            </Link>
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Cari Stakeholder</CardTitle>
              <CardDescription>
                Cari berdasarkan nama, email, atau departemen
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="flex gap-4">
                <Input
                  placeholder="Cari stakeholder..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      handleSearch();
                    }
                  }}
                  className="max-w-sm"
                />
                <Button onClick={handleSearch}>
                  <Search className="mr-2 h-4 w-4" />
                  Cari
                </Button>
                {search && (
                  <Button
                    variant="outline"
                    onClick={() => {
                      setSearch('');
                      router.get('/stakeholders');
                    }}
                  >
                    Reset
                  </Button>
                )}
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Daftar Stakeholder</CardTitle>
              <CardDescription>
                Total {stakeholders.total} stakeholder terdaftar
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Nama</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Posisi</TableHead>
                    <TableHead>Departemen</TableHead>
                    <TableHead>Notifikasi</TableHead>
                    <TableHead className="text-right">Aksi</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {stakeholders.data.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center text-muted-foreground">
                        {search ? 'Tidak ada stakeholder yang sesuai dengan pencarian' : 'Belum ada stakeholder'}
                      </TableCell>
                    </TableRow>
                  ) : (
                    stakeholders.data.map((stakeholder) => (
                      <TableRow key={stakeholder.id}>
                        <TableCell className="font-medium">{stakeholder.name}</TableCell>
                        <TableCell>{stakeholder.email}</TableCell>
                        <TableCell>
                          <span className="text-sm text-muted-foreground">
                            {stakeholder.position || '-'}
                          </span>
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline">{stakeholder.department || 'N/A'}</Badge>
                        </TableCell>
                        <TableCell>
                          {stakeholder.receive_alerts ? (
                            <Badge variant="default">Aktif</Badge>
                          ) : (
                            <Badge variant="secondary">Nonaktif</Badge>
                          )}
                        </TableCell>
                        <TableCell className="text-right">
                          <div className="flex items-center justify-end gap-2">
                            <Link href={`/stakeholders/${stakeholder.id}/edit`}>
                              <Button variant="outline" size="icon">
                                <Pencil className="h-4 w-4" />
                              </Button>
                            </Link>
                            <Button
                              variant="outline"
                              size="icon"
                              onClick={() => handleDelete(stakeholder.id, stakeholder.name)}
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

              {stakeholders.last_page > 1 && (
                <div className="flex items-center justify-between mt-4">
                  <div className="text-sm text-muted-foreground">
                    Halaman {stakeholders.current_page} dari {stakeholders.last_page}
                  </div>
                  <div className="flex gap-2">
                    {stakeholders.current_page > 1 && (
                      <Link
                        href={`/stakeholders?page=${stakeholders.current_page - 1}${search ? `&search=${search}` : ''}`}
                        preserveState
                        preserveScroll
                      >
                        <Button variant="outline">Sebelumnya</Button>
                      </Link>
                    )}
                    {stakeholders.current_page < stakeholders.last_page && (
                      <Link
                        href={`/stakeholders?page=${stakeholders.current_page + 1}${search ? `&search=${search}` : ''}`}
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
