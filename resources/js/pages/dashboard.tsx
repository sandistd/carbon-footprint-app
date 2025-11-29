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
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import {
    AlertCircle,
    AlertTriangle,
    Calendar,
    CheckCircle,
    Factory,
    Leaf,
    Target,
    TrendingDown,
    Truck,
    Users,
    Zap,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    Line,
    LineChart,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface DashboardData {
    scope_1?: {
        total: number;
        count: number;
    };
    scope_2?: {
        total: number;
        count: number;
    };
    scope_3?: {
        total: number;
        count: number;
    };
}

interface PieChartDataItem {
    name: string;
    value: number;
    color: string;
}

interface TargetChartDataItem {
    year: string;
    target: number;
    actual: number | null;
}

interface StakeholderChartDataItem {
    name: string;
    value: number;
}

interface MonthlyChartDataItem {
    month: string;
    total: number;
}

interface Props {
    data: DashboardData;
    grandTotal: number;
    departments: string[];
    filters: {
        scope: string;
        department?: string;
        start_date?: string;
        end_date?: string;
        year?: string;
    };
    availableYears: number[];
    pieChartData: PieChartDataItem[];
    targetChartData: TargetChartDataItem[];
    stakeholderChartData: StakeholderChartDataItem[];
    monthlyChartData: MonthlyChartDataItem[];
    baseline2024: number;
    target2030: number;
}

export default function Dashboard({
    data,
    grandTotal,
    departments,
    filters,
    availableYears,
    pieChartData,
    targetChartData,
    stakeholderChartData,
    monthlyChartData,
    baseline2024,
    target2030,
}: Props) {
    // Default dates: 1 January current year to today
    const currentYear = new Date().getFullYear();
    const defaultStartDate = `${currentYear}-01-01`;
    const defaultEndDate = new Date().toISOString().split('T')[0];

    const [scope, setScope] = useState(filters.scope || 'all');
    const [department, setDepartment] = useState<string>(
        filters.department || 'all',
    );
    const [startDate, setStartDate] = useState(
        filters.start_date || defaultStartDate,
    );
    const [endDate, setEndDate] = useState(filters.end_date || defaultEndDate);
    const [selectedYear, setSelectedYear] = useState<string>(
        filters.year && filters.year.trim() !== ''
            ? filters.year
            : currentYear.toString(),
    );

    // Auto-apply default filters on first load if no filters applied
    useEffect(() => {
        if (!filters.start_date && !filters.end_date && !filters.year) {
            router.get(
                '/dashboard',
                {
                    start_date: defaultStartDate,
                    end_date: defaultEndDate,
                    year: currentYear.toString(),
                },
                {
                    preserveState: true,
                    replace: true,
                },
            );
        }
    }, []);

    const handleFilter = () => {
        router.get(
            '/dashboard',
            {
                scope,
                department: department !== 'all' ? department : undefined,
                start_date: startDate || undefined,
                end_date: endDate || undefined,
                year: selectedYear || undefined,
            },
            {
                preserveState: true,
            },
        );
    };

    const handleReset = () => {
        const currentYear = new Date().getFullYear();
        const defaultStartDate = `${currentYear}-01-01`;
        const defaultEndDate = new Date().toISOString().split('T')[0];

        setScope('all');
        setDepartment('all');
        setStartDate(defaultStartDate);
        setEndDate(defaultEndDate);
        setSelectedYear(currentYear.toString());
        router.get('/dashboard', {
            start_date: defaultStartDate,
            end_date: defaultEndDate,
            year: currentYear.toString(),
        });
    };

    // Custom label for pie chart
    const renderCustomLabel = (entry: PieChartDataItem) => {
        return `${entry.name}: ${entry.value.toLocaleString('id-ID', { minimumFractionDigits: 2 })} Ton`;
    };

    // Function to calculate emission status and alert type
    const getEmissionStatus = () => {
        // Find the current year data in targetChartData
        const currentYearData = targetChartData.find(
            (item) => item.year === selectedYear,
        );

        if (!currentYearData || currentYearData.actual === null) {
            return null;
        }

        const actual = currentYearData.actual;
        const target = currentYearData.target;
        const percentage = (actual / target) * 100;

        if (actual > target) {
            // Actual exceeds target - DANGER
            return {
                type: 'danger',
                icon: AlertCircle,
                message: `Emisi aktual tahun ${selectedYear} sebesar ${actual.toLocaleString('id-ID', { minimumFractionDigits: 2 })} Ton CO2eq melebihi target sebesar ${target.toLocaleString('id-ID', { minimumFractionDigits: 2 })} Ton CO2eq (${percentage.toFixed(1)}% dari target). Diperlukan tindakan segera untuk mengurangi emisi.`,
                className: 'border-red-500 bg-red-50 text-red-800',
            };
        } else if (percentage >= 90) {
            // Actual approaching target (90-100%) - WARNING
            return {
                type: 'warning',
                icon: AlertTriangle,
                message: `Emisi aktual tahun ${selectedYear} sebesar ${actual.toLocaleString('id-ID', { minimumFractionDigits: 2 })} Ton CO2eq mendekati target sebesar ${target.toLocaleString('id-ID', { minimumFractionDigits: 2 })} Ton CO2eq (${percentage.toFixed(1)}% dari target). Perlu monitoring ketat dan strategi mitigasi.`,
                className: 'border-yellow-500 bg-yellow-50 text-yellow-800',
            };
        } else {
            // Actual well below target (<90%) - SUCCESS
            const reduction = (((target - actual) / target) * 100).toFixed(1);
            return {
                type: 'success',
                icon: CheckCircle,
                message: `Emisi aktual tahun ${selectedYear} sebesar ${actual.toLocaleString('id-ID', { minimumFractionDigits: 2 })} Ton CO2eq berada di bawah target sebesar ${target.toLocaleString('id-ID', { minimumFractionDigits: 2 })} Ton CO2eq (${percentage.toFixed(1)}% dari target). Pengurangan emisi mencapai ${reduction}% dari target.`,
                className: 'border-green-500 bg-green-50 text-green-800',
            };
        }
    };

    const emissionStatus = getEmissionStatus();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard - Carbon Footprint" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Filter Section */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filter Data Emisi</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-5">
                            <div>
                                <Label htmlFor="scope">Scope</Label>
                                <Select value={scope} onValueChange={setScope}>
                                    <SelectTrigger id="scope">
                                        <SelectValue placeholder="Pilih Scope" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            Semua Scope
                                        </SelectItem>
                                        <SelectItem value="scope_1">
                                            Scope 1
                                        </SelectItem>
                                        <SelectItem value="scope_2">
                                            Scope 2
                                        </SelectItem>
                                        <SelectItem value="scope_3">
                                            Scope 3
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label htmlFor="department">Department</Label>
                                <Select
                                    value={department}
                                    onValueChange={setDepartment}
                                >
                                    <SelectTrigger id="department">
                                        <SelectValue placeholder="Semua Department" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            Semua Department
                                        </SelectItem>
                                        {departments.map((dept) => (
                                            <SelectItem key={dept} value={dept}>
                                                {dept}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label htmlFor="start_date">
                                    Tanggal Mulai
                                </Label>
                                <Input
                                    id="start_date"
                                    type="date"
                                    value={startDate}
                                    onChange={(e) =>
                                        setStartDate(e.target.value)
                                    }
                                />
                            </div>
                            <div>
                                <Label htmlFor="end_date">Tanggal Akhir</Label>
                                <Input
                                    id="end_date"
                                    type="date"
                                    value={endDate}
                                    onChange={(e) => setEndDate(e.target.value)}
                                />
                            </div>
                            <div className="flex items-end gap-2">
                                <Button
                                    onClick={handleFilter}
                                    className="flex-1"
                                >
                                    Filter
                                </Button>
                                <Button onClick={handleReset} variant="outline">
                                    Reset
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card className="border-l-4 border-l-blue-500">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Emisi
                            </CardTitle>
                            <Leaf className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {grandTotal.toLocaleString('id-ID', {
                                    minimumFractionDigits: 2,
                                })}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Ton CO2eq
                            </p>
                        </CardContent>
                    </Card>

                    {data.scope_1 && (
                        <Card className="border-l-4 border-l-orange-500">
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Scope 1
                                </CardTitle>
                                <Factory className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {data.scope_1.total.toLocaleString(
                                        'id-ID',
                                        { minimumFractionDigits: 2 },
                                    )}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Ton CO2eq ({data.scope_1.count} entries)
                                </p>
                            </CardContent>
                        </Card>
                    )}

                    {data.scope_2 && (
                        <Card className="border-l-4 border-l-yellow-500">
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Scope 2
                                </CardTitle>
                                <Zap className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {data.scope_2.total.toLocaleString(
                                        'id-ID',
                                        { minimumFractionDigits: 2 },
                                    )}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Ton CO2eq ({data.scope_2.count} entries)
                                </p>
                            </CardContent>
                        </Card>
                    )}

                    {data.scope_3 && (
                        <Card className="border-l-4 border-l-green-500">
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Scope 3
                                </CardTitle>
                                <Truck className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {data.scope_3.total.toLocaleString(
                                        'id-ID',
                                        { minimumFractionDigits: 2 },
                                    )}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Ton CO2eq ({data.scope_3.count} entries)
                                </p>
                            </CardContent>
                        </Card>
                    )}
                </div>

                {/* Charts Section */}
                <div className="grid gap-4">
                    {/* Combined Year-based Charts */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle>
                                        Analisis Emisi Tahunan
                                    </CardTitle>
                                    <CardDescription>
                                        Distribusi emisi per scope, stakeholder,
                                        dan tren bulanan
                                    </CardDescription>
                                </div>
                                <div className="w-32">
                                    <Select
                                        value={selectedYear}
                                        onValueChange={(val) => {
                                            setSelectedYear(val);
                                            router.get(
                                                '/dashboard',
                                                {
                                                    scope,
                                                    department:
                                                        department !== 'all'
                                                            ? department
                                                            : undefined,
                                                    start_date:
                                                        startDate || undefined,
                                                    end_date:
                                                        endDate || undefined,
                                                    year:
                                                        val !== 'all'
                                                            ? val
                                                            : undefined,
                                                },
                                                {
                                                    preserveState: true,
                                                },
                                            );
                                        }}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih Tahun" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">
                                                Semua
                                            </SelectItem>
                                            {availableYears.map((year) => (
                                                <SelectItem
                                                    key={year}
                                                    value={year.toString()}
                                                >
                                                    {year}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {/* Alert for emission status */}
                            {emissionStatus && selectedYear !== 'all' && (
                                <Alert
                                    className={`mb-6 ${emissionStatus.className}`}
                                >
                                    <emissionStatus.icon className="h-4 w-4" />
                                    <AlertDescription>
                                        {emissionStatus.message}
                                    </AlertDescription>
                                </Alert>
                            )}

                            <div className="grid gap-6 md:grid-cols-3">
                                {/* Scope Distribution */}
                                <div>
                                    <h3 className="mb-3 flex items-center gap-2 text-sm font-semibold">
                                        <Leaf className="h-4 w-4 text-green-500" />
                                        Distribusi per Scope
                                    </h3>
                                    {pieChartData.length > 0 &&
                                    pieChartData.some((d) => d.value > 0) ? (
                                        <ResponsiveContainer
                                            width="100%"
                                            height={250}
                                        >
                                            <PieChart>
                                                <Pie
                                                    data={pieChartData}
                                                    cx="50%"
                                                    cy="50%"
                                                    labelLine={false}
                                                    label={(entry) =>
                                                        `${entry.name.replace('Scope ', 'S')}`
                                                    }
                                                    outerRadius={70}
                                                    fill="#8884d8"
                                                    dataKey="value"
                                                >
                                                    {pieChartData.map(
                                                        (entry, index) => (
                                                            <Cell
                                                                key={`cell-${index}`}
                                                                fill={
                                                                    entry.color
                                                                }
                                                            />
                                                        ),
                                                    )}
                                                </Pie>
                                                <Tooltip
                                                    formatter={(
                                                        value: number,
                                                    ) =>
                                                        `${value.toLocaleString('id-ID', { minimumFractionDigits: 2 })} Ton`
                                                    }
                                                />
                                                <Legend />
                                            </PieChart>
                                        </ResponsiveContainer>
                                    ) : (
                                        <div className="flex h-[250px] items-center justify-center text-sm text-muted-foreground">
                                            Pilih tahun untuk melihat data
                                        </div>
                                    )}
                                </div>

                                {/* Stakeholder Distribution */}
                                <div>
                                    <h3 className="mb-3 flex items-center gap-2 text-sm font-semibold">
                                        <Users className="h-4 w-4 text-purple-500" />
                                        Distribusi per Stakeholder
                                    </h3>
                                    {stakeholderChartData.length > 0 ? (
                                        <ResponsiveContainer
                                            width="100%"
                                            height={250}
                                        >
                                            <PieChart>
                                                <Pie
                                                    data={stakeholderChartData}
                                                    cx="50%"
                                                    cy="50%"
                                                    labelLine={false}
                                                    label={(entry) =>
                                                        entry.name.split(' ')[0]
                                                    }
                                                    outerRadius={70}
                                                    fill="#8884d8"
                                                    dataKey="value"
                                                >
                                                    {stakeholderChartData.map(
                                                        (entry, index) => (
                                                            <Cell
                                                                key={`cell-${index}`}
                                                                fill={
                                                                    [
                                                                        '#8b5cf6',
                                                                        '#ec4899',
                                                                        '#f59e0b',
                                                                        '#10b981',
                                                                        '#3b82f6',
                                                                        '#6366f1',
                                                                    ][index % 6]
                                                                }
                                                            />
                                                        ),
                                                    )}
                                                </Pie>
                                                <Tooltip
                                                    formatter={(
                                                        value: number,
                                                    ) =>
                                                        `${value.toLocaleString('id-ID', { minimumFractionDigits: 2 })} Ton`
                                                    }
                                                />
                                                <Legend />
                                            </PieChart>
                                        </ResponsiveContainer>
                                    ) : (
                                        <div className="flex h-[250px] items-center justify-center text-sm text-muted-foreground">
                                            Pilih tahun untuk melihat data
                                        </div>
                                    )}
                                </div>

                                {/* Monthly Trend */}
                                <div>
                                    <h3 className="mb-3 flex items-center gap-2 text-sm font-semibold">
                                        <Calendar className="h-4 w-4 text-blue-500" />
                                        Tren Bulanan
                                    </h3>
                                    <ResponsiveContainer
                                        width="100%"
                                        height={250}
                                    >
                                        <BarChart data={monthlyChartData}>
                                            <CartesianGrid strokeDasharray="3 3" />
                                            <XAxis
                                                dataKey="month"
                                                tick={{ fontSize: 10 }}
                                            />
                                            <YAxis
                                                tick={{ fontSize: 10 }}
                                                tickFormatter={(value) =>
                                                    `${(value / 1000).toFixed(0)}K`
                                                }
                                            />
                                            <Tooltip
                                                formatter={(value: number) =>
                                                    `${value.toLocaleString('id-ID', { minimumFractionDigits: 2 })} Ton`
                                                }
                                            />
                                            <Bar
                                                dataKey="total"
                                                fill="#22c55e"
                                                name="Total"
                                            />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Target 2030 Chart */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-start justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2">
                                        <Target className="h-5 w-5 text-blue-500" />
                                        Target Penurunan Emisi 2030
                                    </CardTitle>
                                    <CardDescription>
                                        Target: 45% penurunan dari baseline 2024
                                    </CardDescription>
                                </div>
                                <div className="text-right">
                                    <div className="text-sm font-medium text-muted-foreground">
                                        Baseline 2024
                                    </div>
                                    <div className="text-lg font-bold">
                                        {baseline2024.toLocaleString('id-ID', {
                                            minimumFractionDigits: 2,
                                        })}
                                    </div>
                                    <div className="mt-1 text-sm font-medium text-green-600">
                                        Target 2030
                                    </div>
                                    <div className="text-lg font-bold text-green-600">
                                        {target2030.toLocaleString('id-ID', {
                                            minimumFractionDigits: 2,
                                        })}
                                    </div>
                                    <div className="mt-1 flex items-center justify-end gap-1 text-xs text-muted-foreground">
                                        <TrendingDown className="h-3 w-3" />
                                        45% reduction
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <LineChart data={targetChartData}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="year" />
                                    <YAxis
                                        tickFormatter={(value) =>
                                            `${(value / 1000).toFixed(0)}K`
                                        }
                                    />
                                    <Tooltip
                                        formatter={(value: number) =>
                                            `${value.toLocaleString('id-ID', { minimumFractionDigits: 2 })} Ton CO2eq`
                                        }
                                    />
                                    <Legend />
                                    <Line
                                        type="monotone"
                                        dataKey="target"
                                        stroke="#3b82f6"
                                        strokeWidth={2}
                                        name="Target"
                                        strokeDasharray="5 5"
                                    />
                                    <Line
                                        type="monotone"
                                        dataKey="actual"
                                        stroke="#22c55e"
                                        strokeWidth={2}
                                        name="Aktual"
                                        connectNulls={false}
                                    />
                                </LineChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                </div>

                {/* Information Card */}
                <Card>
                    <CardHeader>
                        <CardTitle>Informasi</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4 text-sm">
                            <div>
                                <h4 className="font-semibold">
                                    Scope 1: Emisi Langsung
                                </h4>
                                <p className="text-muted-foreground">
                                    Emisi dari pembakaran bahan bakar fosil
                                    langsung (Solar untuk Genset BTS dan Bensin
                                    untuk kendaraan operasional)
                                </p>
                            </div>
                            <div>
                                <h4 className="font-semibold">
                                    Scope 2: Emisi Energi Tidak Langsung
                                </h4>
                                <p className="text-muted-foreground">
                                    Emisi dari penggunaan listrik PLN untuk BTS
                                    dan kantor. Dapat dikurangi dengan Renewable
                                    Energy Certificate (REC)
                                </p>
                            </div>
                            <div>
                                <h4 className="font-semibold">
                                    Scope 3: Emisi Rantai Pasok
                                </h4>
                                <p className="text-muted-foreground">
                                    Emisi dari aktivitas lain seperti
                                    distribusi, limbah operasional, perjalanan
                                    bisnis, dan perjalanan karyawan
                                </p>
                            </div>
                            <div className="border-t pt-4">
                                <h4 className="font-semibold">
                                    Target Net Zero 2030
                                </h4>
                                <p className="text-muted-foreground">
                                    PT XL Axiata Tbk berkomitmen untuk
                                    menurunkan emisi sebesar{' '}
                                    <strong>45%</strong> di tahun 2030 dari
                                    baseline tahun 2024, sejalan dengan komitmen
                                    global untuk mengurangi dampak perubahan
                                    iklim.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
