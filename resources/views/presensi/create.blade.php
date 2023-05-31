@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">E-Presensi</div>
        <div class="right"></div>
    </div>

    {{-- CSS --}}
    <style>        
        .webcam-capture,
        .webcam-capture video{
            display: inline-block;
            width: 100% !important;
            margin: auto;
            height: auto !important;
            border-radius: 15px;
        }

        #map { height: 200px; }
    </style>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <!-- * App Header -->
@endsection

@section('content')
    <div class="row" style="margin-top: 70px">
        <div class="col">
            <input type="text" id="lokasi">
            <div class="webcam-capture">

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            @if ($cek > 0)
                <button id="takeAbsen" class="btn btn-danger btn-block">
                    <ion-icon name="camera-outline"></ion-icon>Absen Pulang
                </button>    
            @else
                <button id="takeAbsen" class="btn btn-primary btn-block">
                    <ion-icon name="camera-outline"></ion-icon>Absen Masuk
                </button>
            @endif
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            <div id="map"></div>
        </div>
    </div>

    <audio id="notifikasi_in">
        <source src="{{ asset('assets/sound/notifikasi_in.mp3') }}" type="audio/mpeg">
    </audio>

    <audio id="notifikasi_out">
        <source src="{{ asset('assets/sound/notifikasi_out.mp3') }}" type="audio/mpeg">
    </audio>

    <audio id="notifikasi_radius">
        <source src="{{ asset('assets/sound/notifikasi_radius.mp3') }}" type="audio/mpeg">
    </audio>
@endsection

@push('myscript')
    <script>

        var notifikasi_in       = document.getElementById('notifikasi_in');
        var notifikasi_out      = document.getElementById('notifikasi_out');
        var notifikasi_radius   = document.getElementById('notifikasi_radius');

        Webcam.set({
            height          : 480,
            width           : 640,
            image_format    : 'jpeg',
            jpeg_quality    : 80
        });

        Webcam.attach('.webcam-capture');

        var lokasi = document.getElementById('lokasi');
        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        }

        function successCallback(position){
            lokasi.value = position.coords.latitude + " , " + position.coords.longitude;
            var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 19);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);

            var circle = L.circle([-7.3779449,112.6100028], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: 100 
            }).addTo(map);
        }

        function errorCallback(){

        }


        // JQuery / AJAX
        $("#takeAbsen").click(function(e) {
            Webcam.snap(function(uri){
                image = uri;
            });

            var lokasi = $("#lokasi").val();

            $.ajax({
                type    : 'POST',
                url     : '/presensi/store',
                data    : {
                    _token      : "{{ csrf_token() }}",
                    image       : image,
                    lokasi      : lokasi
                },
                cache   : false,
                success : function(respond){
                    var status = respond.split("|");
                    if(status[0] == "success"){
                        if(status[2] == "in"){
                            notifikasi_in.play();
                        }else{
                            notifikasi_out.play();
                        }

                        Swal.fire({
                            title: 'Success !',
                            text: status[1],
                            icon: 'success'
                        })
                        setTimeout("location.href='/dashboard'", 3000);
                    }else {
                        if(status[2] == "radius"){
                            notifikasi_radius.play();
                        }
                        Swal.fire({
                            title: 'Error !',
                            text: status[1],
                            icon: 'error'
                        });
                    }
                }
            });
        });

    </script>
@endpush