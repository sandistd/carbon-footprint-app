import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft } from 'lucide-react';
import type { User } from '@/types';

interface Stakeholder {
  id: number;
  name: string;
  email: string;
  position?: string;
  department?: string;
  receive_alerts: boolean;
}

interface Props {
  auth: { user: User };
  stakeholder: Stakeholder;
}

export default function Edit({ auth, stakeholder }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    name: stakeholder.name,
    email: stakeholder.email,
    position: stakeholder.position || '',
    department: stakeholder.department || '',
    receive_alerts: stakeholder.receive_alerts,
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    put(`/stakeholders/${stakeholder.id}`);
  };

  return (
    <AppLayout>
      <Head title="Edit Stakeholder" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          <div className="flex items-center gap-4">
            <Link href="/stakeholders">
              <Button variant="outline" size="icon">
                <ArrowLeft className="h-4 w-4" />
              </Button>
            </Link>
            <div>
              <h2 className="text-3xl font-bold tracking-tight">Edit Stakeholder</h2>
              <p className="text-muted-foreground mt-2">
                Perbarui data stakeholder: {stakeholder.name}
              </p>
            </div>
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Informasi Stakeholder</CardTitle>
              <CardDescription>
                Ubah data stakeholder sesuai kebutuhan
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={submit} className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <Label htmlFor="name">
                      Nama <span className="text-destructive">*</span>
                    </Label>
                    <Input
                      id="name"
                      value={data.name}
                      onChange={(e) => setData('name', e.target.value)}
                      required
                      placeholder="Contoh: John Doe"
                    />
                    {errors.name && (
                      <p className="text-sm text-destructive">{errors.name}</p>
                    )}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="email">
                      Email <span className="text-destructive">*</span>
                    </Label>
                    <Input
                      id="email"
                      type="email"
                      value={data.email}
                      onChange={(e) => setData('email', e.target.value)}
                      required
                      placeholder="john.doe@example.com"
                    />
                    {errors.email && (
                      <p className="text-sm text-destructive">{errors.email}</p>
                    )}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="position">Posisi</Label>
                    <Input
                      id="position"
                      value={data.position}
                      onChange={(e) => setData('position', e.target.value)}
                      placeholder="Contoh: Manager"
                    />
                    {errors.position && (
                      <p className="text-sm text-destructive">{errors.position}</p>
                    )}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="department">Departemen</Label>
                    <Input
                      id="department"
                      value={data.department}
                      onChange={(e) => setData('department', e.target.value)}
                      placeholder="Contoh: Operasional"
                    />
                    {errors.department && (
                      <p className="text-sm text-destructive">{errors.department}</p>
                    )}
                  </div>
                </div>

                <div className="flex items-center space-x-2">
                  <Checkbox
                    id="receive_alerts"
                    checked={data.receive_alerts}
                    onCheckedChange={(checked) =>
                      setData('receive_alerts', checked === true)
                    }
                  />
                  <Label
                    htmlFor="receive_alerts"
                    className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                  >
                    Aktifkan notifikasi email untuk stakeholder ini
                  </Label>
                </div>
                {errors.receive_alerts && (
                  <p className="text-sm text-destructive">{errors.receive_alerts}</p>
                )}

                <div className="flex items-center gap-4 pt-4">
                  <Button type="submit" disabled={processing}>
                    {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                  </Button>
                  <Link href="/stakeholders">
                    <Button type="button" variant="outline">
                      Batal
                    </Button>
                  </Link>
                </div>
              </form>
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}
