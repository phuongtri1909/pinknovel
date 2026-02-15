@php
    $messengerUrl = \App\Models\Config::getConfig('messenger_chat_url');
    $messengerEnabled = \App\Models\Config::getConfig('messenger_chat_enabled', 1);
@endphp

@if ($messengerEnabled && $messengerUrl)
    <a href="{{ $messengerUrl }}" target="_blank" rel="noopener noreferrer" id="messengerButton"
        class="messenger-float-btn position-fixed" title="Chat với chúng tôi qua Messenger" style="display: none;">
        {{-- Facebook Messenger Icon --}}
        <?xml version="1.0" encoding="UTF-8"?>
        <svg id="Logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 502 502">
        <defs>
            <style>
            .cls-1 {
                fill: #fff;
            }

            .cls-1, .cls-2 {
                stroke-width: 0px;
            }

            .cls-2 {
                fill: #0866ff;
            }
            </style>
        </defs>
        <path class="cls-2" d="M501,243.5c0,139.34-109.17,242.5-250,242.5-25.29,0-49.56-3.34-72.37-9.61-4.43-1.23-9.14-.88-13.35.97l-49.62,21.91c-12.98,5.73-27.63-3.5-28.06-17.68l-1.36-44.48c-.17-5.48-2.63-10.6-6.72-14.25C30.87,379.36,1,316.39,1,243.5,1,104.16,110.17,1,251,1s250,103.16,250,242.5Z"/>
        <path class="cls-1" d="M318.88,313.31l87.04-134.52c8.75-13.52-7.46-29.26-20.72-20.11l-90.86,62.67c-3.06,2.11-7.1,2.17-10.22.15l-80.65-52.18c-6.83-4.42-15.94-2.46-20.35,4.36l-87.05,134.52c-8.75,13.52,7.46,29.26,20.72,20.11l90.88-62.68c3.06-2.11,7.1-2.17,10.22-.15l80.63,52.17c6.83,4.42,15.94,2.46,20.36-4.36Z"/>
        </svg>
    </a>

    <style>
        .messenger-float-btn {
            bottom: 65px;
            right: 24px;
            z-index: 999;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #0084FF;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0, 132, 255, 0.35);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            cursor: pointer;
            animation: messenger-pulse 2s ease-in-out infinite;
        }

        .messenger-float-btn:hover {
            transform: scale(1.12);
            box-shadow: 0 6px 24px rgba(0, 132, 255, 0.5);
            animation: none;
        }

        .messenger-float-btn:active {
            transform: scale(0.95);
        }

        .messenger-float-btn svg {
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.15));
        }

        @keyframes messenger-pulse {

            0%,
            100% {
                box-shadow: 0 4px 16px rgba(0, 132, 255, 0.35);
            }

            50% {
                box-shadow: 0 4px 24px rgba(0, 132, 255, 0.55), 0 0 0 8px rgba(0, 132, 255, 0.08);
            }
        }

        @media (max-width: 576px) {
            .messenger-float-btn {
                width: 45px;
                height: 45px;
                right: 9px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messengerButton = document.getElementById('messengerButton');
            let messengerLastScrollTop = 0;

            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop < messengerLastScrollTop) {
                    messengerButton.style.display = 'flex';
                } else {
                    messengerButton.style.display = 'none';
                }

                messengerLastScrollTop = scrollTop;
            });
        });
    </script>
@endif
