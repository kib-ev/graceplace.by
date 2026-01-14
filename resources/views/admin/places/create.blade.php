@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Add Place</h1>
            <hr>
            <form action="{{ isset($place) ? route('admin.places.update', $place) : route('admin.places.store') }}" method="post">
                @csrf
                @method(isset($place) ? 'patch' : 'post')

                <div class="form-group mb-2">
                    <label for="name">Имя</label>
                    <input id="name" class="form-control" type="text" name="name" autocomplete="off" value="{{ isset($place) ? $place->name : '' }}">
                </div>

                <div class="form-group mb-2">
                    <label for="description">Описание</label>
                    <textarea id="description" class="form-control" name="description" autocomplete="off">{{ isset($place) ? $place->description : '' }}</textarea>
                </div>

                @if(!isset($place))
                <div class="form-group mb-2">
                    <label for="price_per_hour">Цена за час (BYN) *</label>
                    <input id="price_per_hour" class="form-control @error('price_per_hour') is-invalid @enderror" type="number" step="0.01" min="0" name="price_per_hour" autocomplete="off" value="{{ old('price_per_hour', '') }}" required>
                    @error('price_per_hour')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Укажите начальную цену за час для этого рабочего места. В дальнейшем цены можно изменять через историю цен.
                    </small>
                </div>
                @else
                <div class="alert alert-info">
                    <strong>Price Management:</strong> Prices are now managed through the 
                    <a href="{{ route('admin.places.prices.index', $place) }}">Price History</a> page.
                </div>
                @endif

                <div class="form-group mb-2">
                    <label for="image">Путь к картинке</label>
                    <input id="image" class="form-control" type="text" name="image_path" autocomplete="off" value="{{ isset($place) ? $place->image_path : '' }}">
                </div>

                @if(isset($place))
                <div class="form-group mb-4">
                    <label>Фото рабочего места</label>
                    <div class="mb-2">
                        <input type="file" id="photo-upload" accept="image/*" style="display: none;">
                        <button type="button" class="btn btn-sm btn-success" onclick="document.getElementById('photo-upload').click()">
                            <i class="fas fa-plus-circle"></i> Загрузить фото
                        </button>
                    </div>
                    <div id="photos-container" class="row">
                        @foreach($place->photos as $photo)
                            <div class="col-md-3 mb-3 photo-item" data-photo-id="{{ $photo->id }}">
                                <div class="card">
                                    <img src="{{ Illuminate\Support\Facades\Storage::url($photo->file_path) }}" class="card-img-top" alt="Photo" style="height: 200px; object-fit: cover;">
                                    <div class="card-body p-2">
                                        <button type="button" class="btn btn-sm btn-danger w-100 delete-photo" data-photo-id="{{ $photo->id }}">
                                            <i class="fas fa-trash"></i> Удалить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="form-group mb-2">
                    <label for="sort">Сортировка</label>
                    <input id="sort" class="form-control" type="text" name="sort" autocomplete="off" value="{{ isset($place) ? $place->sort : '' }}">
                </div>

                <div class="form-group">
                    <input type="hidden" name="is_hidden" value="0">
                    <input id="isHidden" class="form-check-input" type="checkbox" name="is_hidden" value="1" {{ isset($place) && $place->is_hidden ? 'checked' : '' }}>
                    <label class="form-check-label" for="isHidden" style="user-select: none;">
                        Скрыто
                    </label>
                </div>

                <hr>

                <div class="form-group">
                    @if(isset($place))
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    @else
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    @endif
                </div>

            </form>
        </div>
    </div>
@endsection

@if(isset($place))
@section('scripts')
<script>
$(document).ready(function() {
    const placeId = {{ $place->id }};
    const uploadUrl = '{{ route("admin.places.photos.store", $place) }}';
    const csrfToken = '{{ csrf_token() }}';
    
    function getDeleteUrl(photoId) {
        return '{{ url("/admin/places") }}/' + placeId + '/photos/' + photoId;
    }

    // Обработка загрузки фото
    $('#photo-upload').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('photo', file);
        formData.append('_token', csrfToken);

        // Показываем индикатор загрузки
        const loadingBtn = $('<button type="button" class="btn btn-sm btn-secondary" disabled>Загрузка...</button>');
        $('#photo-upload').parent().append(loadingBtn);
        $('#photo-upload').parent().find('button.btn-success').hide();

        $.ajax({
            url: uploadUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Добавляем новое фото в контейнер
                    const photoHtml = `
                        <div class="col-md-3 mb-3 photo-item" data-photo-id="${response.photo.id}">
                            <div class="card">
                                <img src="/storage/${response.photo.path}" class="card-img-top" alt="Photo" style="height: 200px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <button type="button" class="btn btn-sm btn-danger w-100 delete-photo" data-photo-id="${response.photo.id}">
                                        <i class="fas fa-trash"></i> Удалить
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#photos-container').append(photoHtml);
                }
            },
            error: function(xhr) {
                alert('Ошибка при загрузке фото: ' + (xhr.responseJSON?.message || 'Неизвестная ошибка'));
            },
            complete: function() {
                loadingBtn.remove();
                $('#photo-upload').parent().find('button.btn-success').show();
                $('#photo-upload').val('');
            }
        });
    });

    // Обработка удаления фото
    $(document).on('click', '.delete-photo', function() {
        const photoId = $(this).data('photo-id');
        const photoItem = $(this).closest('.photo-item');
        
        if (!confirm('Вы уверены, что хотите удалить это фото?')) {
            return;
        }

        $.ajax({
            url: getDeleteUrl(photoId),
            type: 'DELETE',
            data: {
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    photoItem.fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            },
            error: function(xhr) {
                alert('Ошибка при удалении фото: ' + (xhr.responseJSON?.message || 'Неизвестная ошибка'));
            }
        });
    });
});
</script>
@endsection
@endif
