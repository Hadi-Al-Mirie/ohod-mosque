@props([
    'action' => url()->current(),
    'filters' => [],
    // نحتفظ بالقيم القديمة إن وجدت
    'old' => request()->all(),
])

<form id="filter-form" method="GET" action="{{ $action }}">
    <div class="row g-3">
        @foreach ($filters as $f)
            <div class="col-12 col-md-6">
                <label class="form-label">{{ $f['label'] }}</label>

                @php $val = $old[$f['key']] ?? '' @endphp

                @if ($f['type'] === 'select')
                    <select name="{{ $f['key'] }}" class="form-select">
                        <option value="">— اختر {{ $f['label'] }} —</option>
                        @foreach ($f['options'] as $v => $lab)
                            <option value="{{ $v }}" @selected($val == (string) $v)>{{ $lab }}</option>
                        @endforeach
                    </select>
                @elseif($f['type'] === 'date')
                    <input type="date" name="{{ $f['key'] }}" value="{{ $val }}" class="form-control">
                @elseif($f['type'] === 'number')
                    <input type="number" name="{{ $f['key'] }}" value="{{ $val }}" class="form-control"
                        placeholder="{{ $f['label'] }}">
                @else
                    <input type="text" name="{{ $f['key'] }}" value="{{ $val }}" class="form-control"
                        placeholder="{{ $f['label'] }}">
                @endif
            </div>
        @endforeach
    </div>
</form>
