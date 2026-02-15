@php
    $messengerUrl = \App\Models\Config::getConfig('messenger_chat_url');
    $messengerEnabled = \App\Models\Config::getConfig('messenger_chat_enabled', 1);
@endphp

@if ($messengerEnabled && $messengerUrl)
    <a href="{{ $messengerUrl }}" target="_blank" rel="noopener noreferrer" id="messengerButton"
        class="messenger-float-btn position-fixed" title="Chat với chúng tôi qua Messenger">
        {{-- Facebook Messenger Icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800" width="28" height="28">
            <path fill="#fff"
                d="M400 0C174.7 0 0 165.1 0 388c0 116.6 47.8 217.4 125.6 289.6a32.3 32.3 0 0 1 10.8 23.2l2.2 72.2a32.3 32.3 0 0 0 45.3 27.8l80.6-35.5a32.3 32.3 0 0 1 21.5-1.8c36.8 10.2 76 15.6 114 15.6 225.3 0 400-165.1 400-388S625.3 0 400 0z" />
            <path fill="url(#msgGrad)"
                d="M400 0C174.7 0 0 165.1 0 388c0 116.6 47.8 217.4 125.6 289.6a32.3 32.3 0 0 1 10.8 23.2l2.2 72.2a32.3 32.3 0 0 0 45.3 27.8l80.6-35.5a32.3 32.3 0 0 1 21.5-1.8c36.8 10.2 76 15.6 114 15.6 225.3 0 400-165.1 400-388S625.3 0 400 0z" />
            <defs>
                <radialGradient id="msgGrad" cx="101.9" cy="809" r="862.96"
                    gradientTransform="matrix(1 0 0 -1 0 800)" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#09f" />
                    <stop offset=".61" stop-color="#a033ff" />
                    <stop offset=".94" stop-color="#ff5280" />
                    <stop offset="1" stop-color="#ff7061" />
                </radialGradient>
            </defs>
            <path fill="#fff"
                d="M159.8 501.5l117.5-186.4a60 60 0 0 1 86.8-16l93.5 70.1a24 24 0 0 0 29 0l126.2-95.8c16.8-12.8 38.8 7.4 27.6 25.2L522.9 484.9a60 60 0 0 1-86.8 16l-93.5-70.1a24 24 0 0 0-29 0l-126.2 95.8c-16.8 12.8-38.8-7.3-27.6-25.1z" />
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
            background: linear-gradient(135deg, #0695FF 0%, #A033FF 50%, #FF5880 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(6, 149, 255, 0.35);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            cursor: pointer;
            animation: messenger-pulse 2s ease-in-out infinite;
        }

        .messenger-float-btn:hover {
            transform: scale(1.12);
            box-shadow: 0 6px 24px rgba(6, 149, 255, 0.5);
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
                box-shadow: 0 4px 16px rgba(6, 149, 255, 0.35);
            }

            50% {
                box-shadow: 0 4px 24px rgba(6, 149, 255, 0.55), 0 0 0 8px rgba(6, 149, 255, 0.08);
            }
        }

        /* Responsive - nhỏ hơn trên mobile */
        @media (max-width: 576px) {
            .messenger-float-btn {
                width: 45px;
                height: 45px;
                right: 9px;
            }

        }
    </style>
@endif
