@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Pengaturan</h3>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}

        <button type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}

        <button type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>
    </div>
    @endif


    <div class="card shadow-sm border-0">

        {{-- TAB MENU --}}
        <div class="card-header bg-white border-bottom">

            <ul class="nav nav-tabs card-header-tabs">

                <li class="nav-item">
                    <button class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#profil">

                        <i class="bi bi-building me-1"></i>
                        Profil Sekolah

                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#logo">

                        <i class="bi bi-image me-1"></i>
                        Logo Sekolah

                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#password">

                        <i class="bi bi-key me-1"></i>
                        Ganti Password

                    </button>
                </li>

            </ul>

        </div>


        <div class="card-body p-4">

            <div class="tab-content">

                {{-- ========================= --}}
                {{-- PROFIL SEKOLAH --}}
                {{-- ========================= --}}

                <div class="tab-pane fade show active"
                    id="profil">

                    <h5 class="mb-1">Profil Sekolah</h5>

                    <p class="text-muted mb-4">
                        Informasi identitas sekolah.
                    </p>

                    <form action="{{ route('admin.pengaturan.update') }}"
                        method="POST">

                        @csrf
                        @method('PUT')

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Nama Sekolah
                                </label>

                                <input type="text"
                                    name="nama_sekolah"
                                    class="form-control"
                                    value="{{ old(
                                           'nama_sekolah',
                                           $setting->nama_sekolah ?? ''
                                       ) }}">

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    NPSN
                                </label>

                                <input type="text"
                                    name="npsn"
                                    class="form-control"
                                    value="{{ old(
                                           'npsn',
                                           $setting->npsn ?? ''
                                       ) }}">

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Email
                                </label>

                                <input type="email"
                                    name="email"
                                    class="form-control"
                                    value="{{ old(
                                           'email',
                                           $setting->email ?? ''
                                       ) }}">

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Telepon
                                </label>

                                <input type="text"
                                    name="telepon"
                                    class="form-control"
                                    value="{{ old(
                                           'telepon',
                                           $setting->telepon ?? ''
                                       ) }}">

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Website
                                </label>

                                <input type="text"
                                    name="website"
                                    class="form-control"
                                    value="{{ old(
                                           'website',
                                           $setting->website ?? ''
                                       ) }}">

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Kepala Sekolah
                                </label>

                                <input type="text"
                                    name="kepala_sekolah"
                                    class="form-control"
                                    value="{{ old(
                                           'kepala_sekolah',
                                           $setting->kepala_sekolah ?? ''
                                       ) }}">

                            </div>


                            <div class="col-12 mb-3">

                                <label class="form-label">
                                    Alamat
                                </label>

                                <textarea name="alamat"
                                    class="form-control"
                                    rows="3">{{ old(
                                              'alamat',
                                              $setting->alamat ?? ''
                                          ) }}</textarea>

                            </div>

                        </div>

                        <div class="text-end">

                            <button class="btn btn-primary">

                                <i class="bi bi-save me-1"></i>
                                Simpan Profil

                            </button>

                        </div>

                    </form>

                </div>


                {{-- ========================= --}}
                {{-- LOGO SEKOLAH --}}
                {{-- ========================= --}}

                <div class="tab-pane fade"
                    id="logo">

                    <h5>Logo Sekolah</h5>

                    <p class="text-muted">
                        Upload logo resmi sekolah.
                    </p>

                    <div class="row align-items-center mt-4">

                        <div class="col-md-4 text-center mb-4">

                            @if(!empty($setting->logo))

                            <img src="{{ asset(
                                    'storage/' . $setting->logo
                                ) }}"
                                class="img-thumbnail"
                                style="
                                         max-width:180px;
                                         max-height:180px;
                                     ">

                            @else

                            <div class="border rounded p-5 bg-light">

                                <i class="
                                        bi bi-image
                                        fs-1
                                        text-muted
                                    "></i>

                                <p class="text-muted mt-2 mb-0">
                                    Belum ada logo
                                </p>

                            </div>

                            @endif

                        </div>


                        <div class="col-md-8">

                            <form action="{{
                                      route(
                                          'admin.pengaturan.update'
                                      )
                                  }}"
                                method="POST"
                                enctype="multipart/form-data">

                                @csrf
                                @method('PUT')

                                <label class="form-label">
                                    Pilih Gambar
                                </label>

                                <input type="file"
                                    name="logo"
                                    class="form-control mb-3"
                                    accept=".jpg,.jpeg,.png">

                                <small class="text-muted d-block mb-3">
                                    Format JPG atau PNG.
                                </small>

                                <button class="btn btn-primary">

                                    <i class="bi bi-upload me-1"></i>
                                    Upload Logo

                                </button>

                            </form>

                        </div>

                    </div>

                </div>

                {{-- ========================= --}}
                {{-- PASSWORD --}}
                {{-- ========================= --}}

                <div class="tab-pane fade"
                    id="password">

                    <h5>Ganti Password Admin</h5>

                    <p class="text-muted mb-4">
                        Perbarui password akun admin yang sedang login.
                    </p>

                    <form action="{{
                              route(
                                  'admin.pengaturan.password'
                              )
                          }}"
                        method="POST"
                        style="max-width:600px;">

                        @csrf
                        @method('PUT')


                        <div class="mb-3">

                            <label class="form-label">
                                Password Lama
                            </label>

                            <input type="password"
                                name="password_lama"
                                class="form-control">

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Password Baru
                            </label>

                            <input type="password"
                                name="password"
                                class="form-control">

                        </div>


                        <div class="mb-4">

                            <label class="form-label">
                                Konfirmasi Password Baru
                            </label>

                            <input type="password"
                                name="password_confirmation"
                                class="form-control">

                        </div>


                        <button class="btn btn-primary">

                            <i class="bi bi-key me-1"></i>
                            Ganti Password

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection