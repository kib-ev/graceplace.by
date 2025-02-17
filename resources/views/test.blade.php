<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="{{ asset('/libs/select-box-search-option/js/jquery-searchbox.js') }}"></script>
    <link href="{{ asset('/libs/select-box-search-option/css/common.css') }}" rel="stylesheet">

</head>
<body>
<select name="lang" class="js-searchBox">
    <option value="">Select A Language</option>
    <option value="1">Python</option>
    <option value="2">Java</option>
    <option value="3">Ruby</option>
    <option value="4">C/C++</option>
    <option value="5">C#</option>
    <option value="6">JavaScript</option>
    <option value="7">PHP</option>
    <option value="8">Swift</option>
    <option value="9">Scala</option>
    <option value="10">R</option>
    <option value="11">Go</option>
    <option value="12">VisualBasic.NET</option>
    <option value="13">Kotlin</option>
</select>

<script>
    $('.js-searchBox').searchBox({ elementWidth: '250'});
</script>
<w></w>

{{--<script>--}}
{{--    $(document).ready(function () {--}}
{{--        $('.js-searchBox').searchBox();--}}
{{--    });--}}
{{--</script>--}}
</body>
</html>
