<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://my.petra.ac.id/css/css.css">
    <link rel="stylesheet" href="https://my.petra.ac.id/adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://my.petra.ac.id/adminlte/dist/css/adminlte.min.css?v=3.2.0">
    <link rel="stylesheet" href="https://my.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="stylesheet" href="https://my.petra.ac.id/flexbox/flexbox.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        .navbar {
            height: 80px !important;
            /*i assume your navbar size 100px*/
            color: #6c757d;
            font-weight: bold;
            border-bottom: 3px #f8ad3d solid;
        }

        .navbar-nav .nav-link {
            color: #6c757d !important;
        }

        .nav-item.dropdown:hover .nav-link {
        background-color: #6c757d;
        color: white !important;
    }

        @media only screen and (min-width: 600px) {
            .navbar {
                height: 80px !important;
                color: #6c757d;
                font-weight: bold;
            }
        }

        @media only screen and (min-width: 768px) {
            .navbar {
                height: 80px !important;
                color: #6c757d;
                font-weight: bold;
            }
        }

        .setting-profile {
            color: #6c757d;
            text-decoration: none;
            font-size: 15px;
        }

    </style>
</head>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="https://my.petra.ac.id/img/logo.png" alt="Gate">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <a href="https://my.petra.ac.id" class="ml-2">
                <img src="https://my.petra.ac.id/img/logo.png" alt="Gate" style="width: 153px;">
            </a>
            <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link btn btn-outline-secondary" data-toggle="dropdown" href="#">
                        {{ strtoupper(auth()->user()->name) }} <i class="fas fa-user-circle"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <div class="mediax">
                            <div class="card-body box-profile">
                                <div class="text-center">
                                    <img class="profile-user-img img-fluid img-circle" src="https://my.petra.ac.id/img/user.png" alt="User profile picture">
                                </div>
                                <h3 class="profile-username text-center">
                                    {{ strtoupper(auth()->user()->name) }}
                                    <a href="{{ route('profile.staff.setting') }}" class="setting-profile"><i class="fas fa-pencil-alt"></i></a>
                                </h3>
                                <p class="text-muted text-center">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <center>
                            <a href="{{ route('logout') }}" class="btn btn-danger mb-2" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><b>Logout</b></a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </center>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="col-sm-8 mt-3 mb-2" style="margin-left: auto; margin-right: auto;">
                    <form action="{{ url('/') }}" method="GET">
                        <div class="input-group input-group-md">
                            <input class="form-control form-control-lg" type="search" id="search" name="search" value="" placeholder="Cari Aplikasi" aria-label="Search App">
                            <div class="input-group-append">
                                <button class="btn btn-navbar" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flexgrid">
                <div class="mb-3 container" style="text-align: left;">
                    <h1 class="col-lg-5" style="text-align: left; font-weight: 550; color: #1E3258; border-bottom: 3px #f8ad3d solid;">
                        Default
                    </h1>
                </div>
                <div class="container">
                    <a class="thing" href="https://my.petra.ac.id">
                        <i class="fas fa-torii-gate"></i> GATE
                    </a>
                </div>

                <!-- Add similar sections dynamically here if needed -->
                 <!-- Akademik Section -->
                 <div class="mb-3 container" style="text-align: left;">
                    <h1 class="col-lg-5" style="text-align: left; font-weight: 550; color: #1E3258; border-bottom: 3px #f8ad3d solid;">
                        Akademik
                    </h1>
                </div>
                <div class="container">
                    <a class="thing" href="https://bap.petra.ac.id">
                        <i class="fas fa-chalkboard-teacher"></i> BAP
                    </a>
                    <a class="thing" href="https://leap.petra.ac.id">
                        <i class=""></i> BIMBINGAN MAHASISWA
                    </a>
                    <a class="thing" href="https://service-learning.petra.ac.id">
                        <i class=""></i> LEAP-MBKM
                    </a>
                    <a class="thing" href="https://leap.petra.ac.id">
                        <i class=""></i> OBE
                    </a>
                    <a class="thing" href="https://service-learning.petra.ac.id">
                        <i class=""></i> SERVICE LEARNING
                    </a>
                </div>

                <!-- Lainnya Section -->
                <div class="mb-3 container" style="text-align: left;">
                    <h1 class="col-lg-5" style="text-align: left; font-weight: 550; color: #1E3258; border-bottom: 3px #f8ad3d solid;">
                        Lainnya
                    </h1>
                </div>
                <div class="container">
                    <a class="thing" href="https://form.petra.ac.id">
                        <i class="fas fa-question-circle"></i> Petra Form
                    </a>
                    <a class="thing" href="https://events.petra.ac.id">
                        <i class=""></i> Event Website
                    </a>
                    <a class="thing" href="http://lostnfound.petra.ac.id">
                        <i class="fas fa-person-circle-plus"></i> LOST &amp; FOUND
                    </a>
                    <a class="thing" href="https://konseling.petra.ac.id">
                        <i class="fas fa-user-friends"></i> Konseling
                    </a>
                </div>
                <!-- Personalia Section -->
                <div class="mb-3 container" style="text-align: left;">
                    <h1 class="col-lg-5" style="text-align: left; font-weight: 550; color: #1E3258; border-bottom: 3px #f8ad3d solid;">
                        PERSONALIA &amp; P2M
                    </h1>
                </div>
                <div class="container">
                    <a class="thing" href="https://sim.petra.ac.id">
                        <i class="fas fa-cubes"></i> SPMS
                    </a>
                    <a class="thing" href="https://sim-eltc.petra.ac.id">
                        <i class=""></i> ABDIMAS
                    </a>
                    <a class="thing" href="https://tnc.petra.ac.id">
                        <i class=""></i> GRANT
                    </a>
                    <a class="thing" title="" href="https://survei-alumni.petra.ac.id">
                        <i class=""></i> PENUGASAN
                    </a>
                </div>

                <!-- Apps Section -->
                <div class="mb-3 container" style="text-align: left;">
                    <h1 class="col-lg-5" style="text-align: left; font-weight: 550; color: #1E3258; border-bottom: 3px #f8ad3d solid;">
                        Apps
                    </h1>
                </div>
                <div class="container">
                    <a class="thing" href="https://sim.petra.ac.id">
                        <i class="fas fa-cubes"></i> SIM UK Petra
                    </a>
                    <a class="thing" href="https://sim-eltc.petra.ac.id">
                        <i class=""></i> SIM-ELTC
                    </a>
                    <a class="thing" href="https://tnc.petra.ac.id">
                        <i class=""></i> Term &amp; Conditions
                    </a>
                    <a class="thing" title="" href="https://survei-alumni.petra.ac.id">
                        <i class=""></i> Survei Alumni
                    </a>
                </div>

                <!-- Pelaporan Section -->
                <div class="mb-3 container" style="text-align: left;">
                    <h1 class="col-lg-5" style="text-align: left; font-weight: 550; color: #1E3258; border-bottom: 3px #f8ad3d solid;">
                        Pelaporan
                    </h1>
                </div>
                <div class="container">
                    <a class="thing" href="https://sim.petra.ac.id">
                        <i class="fas fa-cubes"></i> SISTER
                    </a>
                </div>


                <!-- Link Section -->
                <div class="mb-3 container" style="text-align: left;">
                    <h1 class="col-lg-5" style="text-align: left; font-weight: 550; color: #1E3258; border-bottom: 3px #f8ad3d solid;">
                        Link
                    </h1>
                </div>
                <div class="container">
                    <a class="thing" href="https://s.petra.ac.id/">
                        <i class=""></i> Shortener
                    </a>
                    <a class="thing" href="https://sim-eltc.petra.ac.id">
                        <i class="fas fa-shield-alt"></i> Activate MFA
                    </a>
                </div>

                <!-- Mutu Section -->
                <div class="mb-3 container" style="text-align: left;">
                    <h1 class="col-lg-5" style="text-align: left; font-weight: 550; color: #1E3258; border-bottom: 3px #f8ad3d solid;">
                        Mutu
                    </h1>
                </div>
                <div class="container">
                    <a class="thing" href="https://survey.petra.ac.id">
                        <i class="fa fa-question"></i> Survey
                    </a>
                    <a class="thing" href="https://sim-eltc.petra.ac.id">
                        <i class=""></i> AKREDITASI
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2023 <a href="https://petra.ac.id">Petra Christian University</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                Pusat Pengembangan Sistem Informasi <span>version: v1.0.15</span>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://my.petra.ac.id/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="https://my.petra.ac.id/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://my.petra.ac.id/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>
    <script src="https://my.petra.ac.id/adminlte/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="https://my.petra.ac.id/flexbox/flexbox.js"></script>

    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        function showLoading() {
            Swal.fire({
                title: 'Loading ...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
        }
        const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        showCloseButton: true,
        timer: 5000
    });
    </script>
</body>

</html>
