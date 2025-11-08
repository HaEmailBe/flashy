<form method="GET">
    <div class="d-flex justify-content-end">
        <div class="input-group mb-3">
            <div class="col-10">
                <select id="search-id" class="form-select" name="id" onchange="this.form.submit()">
                    <option value="" selected>All Slugs</option>
                    @foreach ($slugs as $id => $slug)
                        <option value="{{ $id }}" @if ($id == request()->query('id')) selected @endif>
                            {{ $slug }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-1">
                <div class="input-group-append text-end">
                    @if (request()->filled('id'))
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="document.querySelector('#search-id').value = '', this.form.submit()">
                            <i class="fa fa-refresh"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>
