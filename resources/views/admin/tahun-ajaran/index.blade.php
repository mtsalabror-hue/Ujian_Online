@extends('layouts.admin')

@section('title', 'Data Tahun Ajaran')

@section('content')

<div class="card shadow-sm">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
            <i class="bi bi-calendar3"></i>
            Data Tahun Ajaran
        </h4>

        <a href="{{ route('admin.tahun-ajaran.create') }}"
            class="btn btn-primary">

            <i class="bi bi-plus-circle"></i>
            Tambah Tahun Ajaran

        </a>

    </div>

    <div class="card-body">

        {{-- Alert Success --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>
        @endif

        {{-- Alert Error --}}
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>
        @endif

        {{-- Form Search --}}
        <form action="{{ route('admin.tahun-ajaran.index') }}"
            method="GET"
            class="mb-3">

            <div class="row">

                <div class="col-md-4">

                    <input
                        type="text"
                        name="keyword"
                        class="form-control"
                        placeholder="Cari Tahun Ajaran..."
                        value="{{ request('keyword') }}">

                </div>

                <div class="col-md-2">

                    <button class="btn btn-secondary">

                        <i class="bi bi-search"></i>

                        Cari

                    </button>

                </div>

            </div>

        </form>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-dark">

                    <tr>

                        <th width="70">No</th>

                        <th>Tahun Ajaran</th>

                        <th>Semester</th>

                        <th>Status</th>

                        <th width="170">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($tahunAjaran as $item)

                    <tr>

                        <td>

                            {{ $tahunAjaran->firstItem() + $loop->index }}

                        </td>

                        <td>

                            {{ $item->tahun_ajaran }}

                        </td>

                        <td>

                            {{ $item->semester }}

                        </td>

                        <td>

                            @if($item->status)

                            <span class="badge bg-success">

                                Aktif

                            </span>

                            @else

                            <span class="badge bg-secondary">

                                Tidak Aktif

                            </span>

                            @endif

                        </td>

                        <td class="text-center">

                            <a href="{{ route('admin.tahun-ajaran.show',$item) }}"
                                class="btn btn-info btn-sm">

                                <i class="bi bi-eye"></i>

                            </a>

                            <a href="{{ route('admin.tahun-ajaran.edit',$item) }}"
                                class="btn btn-warning btn-sm">

                                <i class="bi bi-pencil-square"></i>

                            </a>

                            <form
                                action="{{ route('admin.tahun-ajaran.destroy',$item) }}"
                                method="POST"
                                class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus data ini?')">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="5" class="text-center">

                            Belum ada data Tahun Ajaran.

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">

            {{ $tahunAjaran->links() }}

        </div>

    </div>

</div>

@endsection