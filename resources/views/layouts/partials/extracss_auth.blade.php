   <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        body {
            background-color: #243949;
        }

        h1 {
            color: #fff;
        }
    </style>

    <style type="text/css">
        /*
      * Pattern lock css
      * Pattern direction
      * http://ignitersworld.com/lab/patternLock.html
      */
        .patt-wrap {
            z-index: 10;
        }

        .patt-circ.hovered {
            background-color: #cde2f2;
            border: none;
        }

        .patt-circ.hovered .patt-dots {
            display: none;
        }

        .patt-circ.dir {
            background-image: url("http://pos.test/img/pattern-directionicon-arrow.png");
            background-position: center;
            background-repeat: no-repeat;
        }

        .patt-circ.e {
            -webkit-transform: rotate(0);
            transform: rotate(0);
        }

        .patt-circ.s-e {
            -webkit-transform: rotate(45deg);
            transform: rotate(45deg);
        }

        .patt-circ.s {
            -webkit-transform: rotate(90deg);
            transform: rotate(90deg);
        }

        .patt-circ.s-w {
            -webkit-transform: rotate(135deg);
            transform: rotate(135deg);
        }

        .patt-circ.w {
            -webkit-transform: rotate(180deg);
            transform: rotate(180deg);
        }

        .patt-circ.n-w {
            -webkit-transform: rotate(225deg);
            transform: rotate(225deg);
        }

        .patt-circ.n {
            -webkit-transform: rotate(270deg);
            transform: rotate(270deg);
        }

        .patt-circ.n-e {
            -webkit-transform: rotate(315deg);
            transform: rotate(315deg);
        }
    </style>
    <style>
        body {
            background: linear-gradient(to right, #6366f1, #3b82f6);
            min-height: 100vh;
        }

        h1 {
            color: #fff;
        }

        /* Mobile-specific background adjustments */
        @media (max-width: 768px) {
            body {
                background: linear-gradient(135deg, #6366f1, #3b82f6);
                background-attachment: fixed;
            }
        }

        /* Prevent horizontal scroll on mobile */
        html, body {
            overflow-x: hidden;
            width: 100%;
        }

        /* Touch-friendly improvements */
        @media (max-width: 768px) {
            /* Ensure touch targets are at least 44px */
            a, button, input, select, textarea {
                min-height: 44px;
            }

            /* Improve tap targets */
            .btn, .form-control, .input-group-addon {
                min-height: 48px;
            }

            /* Better spacing for mobile */
            .form-group {
                margin-bottom: 20px;
            }

            /* Responsive text sizing */
            .wizard .content {
                font-size: 16px;
            }
        }
    </style>
    <style>
        .action-link[data-v-1552a5b6] {
            cursor: pointer;
        }
    </style>
    <style>
        .action-link[data-v-397d14ca] {
            cursor: pointer;
        }
    </style>
    <style>
        .action-link[data-v-49962cc0] {
            cursor: pointer;
        }
    </style>

<link href="{{ asset('css/tailwind/app.css') }}" rel="stylesheet">
