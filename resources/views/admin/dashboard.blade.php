@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card text-white h-100" style="background: linear-gradient(135deg, #005922, #003d17);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 opacity-75">Produtos</h6>
                        <h2 class="mb-0 fw-bold">{{ $totalProdutos }}</h2>
                    </div>
                    <i class="bi bi-box fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card text-white h-100" style="background: linear-gradient(135deg, #A7D02A, #7fa020);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 opacity-75">Coletas</h6>
                        <h2 class="mb-0 fw-bold">{{ $totalColetas }}</h2>
                    </div>
                    <i class="bi bi-clipboard-data fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card text-white h-100" style="background: linear-gradient(135deg, #FFD100, #cca700);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 opacity-75">Lojas</h6>
                        <h2 class="mb-0 fw-bold">{{ $totalLojas }}</h2>
                    </div>
                    <i class="bi bi-shop fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card text-white h-100" style="background: linear-gradient(135deg, #F01516, #b01011);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 opacity-75">Usuários</h6>
                        <h2 class="mb-0 fw-bold">{{ $totalUsuarios }}</h2>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Resumo do Sistema</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">
                    <i class="bi bi-upc-scan"></i> Datacheck - Medeiros v1.0.0
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
