@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Usuário</h4>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text"
                           class="form-control @error('name') is-invalid @enderror"
                           id="name"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="position" class="form-label">Cargo</label>
                    <select class="form-select @error('position') is-invalid @enderror"
                            id="position"
                            name="position"
                            required>
                        <option value="">Selecione um cargo</option>
                        <option value="ADMIN" {{ old('position', $user->position) == 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                        <option value="GERENCIA" {{ old('position', $user->position) == 'GERENCIA' ? 'selected' : '' }}>GERENCIA</option>
                        <option value="COLETOR" {{ old('position', $user->position) == 'COLETOR' ? 'selected' : '' }}>COLETOR</option>
                    </select>
                    @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">
                        Nova Senha <small class="text-muted">(deixe em branco para manter)</small>
                    </label>
                    <input type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           id="password"
                           name="password"
                           minlength="6">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                    <input type="password"
                           class="form-control"
                           id="password_confirmation"
                           name="password_confirmation">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="coleta_edit"
                               name="coleta_edit"
                               value="1"
                               {{ old('coleta_edit', $user->coleta_edit) ? 'checked' : '' }}>
                        <label class="form-check-label" for="coleta_edit">
                            Pode editar coletas
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="coleta_delete"
                               name="coleta_delete"
                               value="1"
                               {{ old('coleta_delete', $user->coleta_delete) ? 'checked' : '' }}>
                        <label class="form-check-label" for="coleta_delete">
                            Pode excluir coletas
                        </label>
                    </div>
                </div>
            </div>

            @if ($lojas->isNotEmpty())
            <div class="mb-3">
                <label class="form-label">Lojas com acesso</label>
                <p class="text-muted small">Se nenhuma loja for selecionada, o usuário terá acesso a todas as lojas.</p>
                <div class="row" style="max-height: 200px; overflow-y: auto;">
                    @foreach ($lojas as $loja)
                    <div class="col-md-4 col-lg-3 mb-1">
                        <div class="form-check">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="loja_{{ $loja->id }}"
                                   name="lojas[]"
                                   value="{{ $loja->id }}"
                                   {{ in_array($loja->id, old('lojas', $user->lojas->pluck('id')->toArray())) ? 'checked' : '' }}>
                            <label class="form-check-label" for="loja_{{ $loja->id }}">
                                {{ $loja->nome }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                @error('lojas')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            @endif

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Atualizar
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
