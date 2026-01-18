<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .field {
            position: absolute;
            white-space: nowrap;
        }
    </style>
</head>
<body>

<div class="page">
    <img 
        src="{{ public_path('storage/' . $template->background_path) }}"
        style="width:100%; height:auto;"
    />

    @foreach ($template->fields as $key => $field)
        <div class="field"
            style="
                left: {{ $field['x'] }}px;
                top: {{ $field['y'] }}px;
                font-size: {{ $field['fontSize'] }}px;
                color: {{ $field['color'] }};
                text-align: {{ $field['align'] }};
            "
        >
            {{ $values[$key] ?? '' }}
        </div>
    @endforeach
</div>

</body>
</html>