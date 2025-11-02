<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockDevTools
{
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ chặn khi app.debug = false
        if (!config('app.debug')) {
            $response = $next($request);
            $script = $this->getBlockScript();
            $content = $response->getContent();
            
            // Inject script trước thẻ </body>
            if (strpos($content, '</body>') !== false) {
                $content = str_replace('</body>', $script . '</body>', $content);
                $response->setContent($content);
            }
            
            return $response;
        }
        
        return $next($request);
    }
    
    private function getBlockScript(): string
    {
        return '
        <script>
        (function() {
            "use strict";
            
            // Kiểm tra xem element có được phép copy/paste không
            function isAllowedElement(target) {
                if (!target) return false;
                
                // Cho phép input, textarea, select
                if (target.tagName === "INPUT" || 
                    target.tagName === "TEXTAREA" || 
                    target.tagName === "SELECT" ||
                    target.isContentEditable) {
                    return true;
                }
                
                // Cho phép các element có class allow-copy hoặc trong phạm vi cho phép
                const allowedClasses = [
                    "allow-copy",
                    "payment-info-value",
                    "payment-content-text",
                    "copy-button",
                    "ckeditor",
                    "cke",
                    "cke_contents",
                    "cke_editable",
                    "comment-input",
                    "form-control",
                    "form-select"
                ];
                
                for (let className of allowedClasses) {
                    if (target.classList && target.classList.contains(className)) {
                        return true;
                    }
                    // Kiểm tra parent
                    let parent = target.closest("." + className);
                    if (parent) return true;
                }
                
                // Kiểm tra xem có nằm trong CKEditor không
                if (target.closest(".ckeditor") || 
                    target.closest(".cke") || 
                    target.closest(".cke_contents") ||
                    target.closest(".cke_editable") ||
                    (target.id && target.id.indexOf("cke_") === 0) ||
                    target.closest("[id*=\"cke_\"]")) {
                    return true;
                }
                
                return false;
            }
            
            // Chặn DevTools - Nhiều cách mở hơn
            document.addEventListener("keydown", function(e) {
                // F12 hoặc DevTools
                if (e.key === "F12" || e.keyCode === 123) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
                
                // Ctrl + Shift + I (Inspect)
                if (e.ctrlKey && e.shiftKey && (e.key === "I" || e.keyCode === 73)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
                
                // Ctrl + Shift + C (Inspect Element)
                if (e.ctrlKey && e.shiftKey && (e.key === "C" || e.keyCode === 67)) {
                    // Cho phép nếu đang trong element được phép
                    if (!isAllowedElement(e.target)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }
                
                // Ctrl + Shift + J (Console)
                if (e.ctrlKey && e.shiftKey && (e.key === "J" || e.keyCode === 74)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
                
                // Ctrl + Shift + K (Console Firefox)
                if (e.ctrlKey && e.shiftKey && (e.key === "K" || e.keyCode === 75)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
                
                // Ctrl + U (View Source)
                if (e.ctrlKey && (e.key === "u" || e.key === "U" || e.keyCode === 85)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
                
                // Ctrl + Shift + Del (Clear browsing data)
                if (e.ctrlKey && e.shiftKey && (e.key === "Delete" || e.keyCode === 46)) {
                    e.preventDefault();
                    return false;
                }
                
                // Ctrl + P (Print - có thể xem source)
                if (e.ctrlKey && (e.key === "p" || e.key === "P" || e.keyCode === 80)) {
                    // Cho phép trong input/textarea để tìm kiếm
                    if (!isAllowedElement(e.target)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }
                
                // Ctrl + S (Save page)
                if (e.ctrlKey && (e.key === "s" || e.key === "S" || e.keyCode === 83)) {
                    // Cho phép trong input/textarea để lưu form
                    if (!isAllowedElement(e.target)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }
                
                // Chặn Copy - Chỉ chặn khi không phải element được phép
                if (e.ctrlKey && (e.key === "c" || e.key === "C" || e.keyCode === 67)) {
                    if (!isAllowedElement(e.target)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }
                
                // Chặn Cut - Chỉ chặn khi không phải element được phép
                if (e.ctrlKey && (e.key === "x" || e.key === "X" || e.keyCode === 88)) {
                    if (!isAllowedElement(e.target)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }
                
                // Chặn Paste - Chỉ chặn khi không phải element được phép
                if (e.ctrlKey && (e.key === "v" || e.key === "V" || e.keyCode === 86)) {
                    if (!isAllowedElement(e.target)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }
                
                // Chặn Select All - Chỉ chặn khi không phải element được phép
                if (e.ctrlKey && (e.key === "a" || e.key === "A" || e.keyCode === 65)) {
                    if (!isAllowedElement(e.target)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }
                
                // Chặn F5 và Ctrl+R (Refresh) - Có thể giữ lại để user có thể reload
                // Nhưng nếu muốn chặn hoàn toàn thì uncomment
                // if (e.key === "F5" || (e.ctrlKey && (e.key === "r" || e.key === "R" || e.keyCode === 82))) {
                //     e.preventDefault();
                //     return false;
                // }
                
            }, true);
            
            // Chặn chuột phải (Context Menu) - Cho phép trong element được phép
            document.addEventListener("contextmenu", function(e) {
                // Cho phép context menu trong input, textarea và các element được phép
                if (!isAllowedElement(e.target)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);
            
            // Chặn Copy Event - Cho phép trong element được phép
            document.addEventListener("copy", function(e) {
                if (!isAllowedElement(e.target)) {
                    e.preventDefault();
                    e.clipboardData.setData("text/plain", "");
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);
            
            // Chặn Cut Event - Cho phép trong element được phép
            document.addEventListener("cut", function(e) {
                if (!isAllowedElement(e.target)) {
                    e.preventDefault();
                    e.clipboardData.setData("text/plain", "");
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);
            
            // Chặn Paste Event - Cho phép trong element được phép
            document.addEventListener("paste", function(e) {
                if (!isAllowedElement(e.target)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);
            
            // Chặn Select Text - Cho phép trong element được phép
            document.addEventListener("selectstart", function(e) {
                if (!isAllowedElement(e.target)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);
            
            // Chặn Drag Start - Cho phép trong element được phép
            document.addEventListener("dragstart", function(e) {
                if (!isAllowedElement(e.target)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);
            
            // Chặn Drag - Cho phép trong element được phép
            document.addEventListener("drag", function(e) {
                if (!isAllowedElement(e.target)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, true);
            
            // CSS: Disable text selection globally, nhưng cho phép trong element được phép
            const style = document.createElement("style");
            style.textContent = `
                * {
                    -webkit-user-select: none !important;
                    -moz-user-select: none !important;
                    -ms-user-select: none !important;
                    user-select: none !important;
                    -webkit-user-drag: none !important;
                    user-drag: none !important;
                }
                
                input, textarea, select, 
                [contenteditable="true"],
                .allow-copy,
                .payment-info-value,
                .payment-content-text,
                .copy-button,
                .comment-input,
                .form-control,
                .form-select,
                .ckeditor,
                .ckeditor *,
                .cke,
                .cke *,
                .cke_contents,
                .cke_contents *,
                .cke_editable,
                .cke_editable * {
                    -webkit-user-select: text !important;
                    -moz-user-select: text !important;
                    -ms-user-select: text !important;
                    user-select: text !important;
                    -webkit-user-drag: auto !important;
                    user-drag: auto !important;
                }
                
                [id*="cke_"], [id*="cke_"] * {
                    -webkit-user-select: text !important;
                    -moz-user-select: text !important;
                    -ms-user-select: text !important;
                    user-select: text !important;
                    -webkit-user-drag: auto !important;
                    user-drag: auto !important;
                }
                
                img, a {
                    -webkit-user-drag: none !important;
                    user-drag: none !important;
                    pointer-events: none !important;
                }
                
                a[href] {
                    pointer-events: auto !important;
                }
            `;
            document.head.appendChild(style);
            
            // Disable image drag
            document.addEventListener("DOMContentLoaded", function() {
                const images = document.querySelectorAll("img");
                images.forEach(function(img) {
                    img.setAttribute("draggable", "false");
                    img.ondragstart = function() { return false; };
                    img.style.pointerEvents = "none";
                    // Cho phép click vào ảnh nếu là link
                    const link = img.closest("a");
                    if (link) {
                        img.style.pointerEvents = "auto";
                        link.style.pointerEvents = "auto";
                    }
                });
            });
            
            // Chặn DevTools bằng cách detect - Nhẹ nhàng hơn
            let devtools = {
                open: false,
                count: 0
            };
            
            const threshold = 160;
            setInterval(function() {
                const heightDiff = window.outerHeight - window.innerHeight;
                const widthDiff = window.outerWidth - window.innerWidth;
                
                if (heightDiff > threshold || widthDiff > threshold) {
                    if (!devtools.open) {
                        devtools.open = true;
                        devtools.count++;
                        
                        // Cảnh báo sau 3 lần phát hiện
                        if (devtools.count >= 3) {
                            console.clear();
                            // Có thể redirect hoặc cảnh báo
                            // window.location.href = "/";
                        }
                    }
                } else {
                    if (devtools.open) {
                        devtools.count = 0;
                    }
                    devtools.open = false;
                }
            }, 500);
            
            // Chặn console
            const noop = function() {};
            const methods = [
                "log", "debug", "info", "warn", "error",
                "assert", "clear", "count", "dir", "dirxml",
                "group", "groupCollapsed", "groupEnd", "profile",
                "profileEnd", "time", "timeEnd", "timeStamp",
                "table", "trace"
            ];
            
            for (let i = 0; i < methods.length; i++) {
                console[methods[i]] = noop;
            }
            
            // Chặn cách mở DevTools bằng cách inspect element từ chuột phải
            document.addEventListener("mousedown", function(e) {
                if (e.button === 2) { // Right click
                    if (!isAllowedElement(e.target)) {
                        e.preventDefault();
                        return false;
                    }
                }
            }, true);
            
            // Chặn cách inspect qua menu
            document.addEventListener("keydown", function(e) {
                // Chặn Ctrl+Shift+C khi không phải trong element được phép
                if (e.ctrlKey && e.shiftKey && e.keyCode === 67 && !isAllowedElement(e.target)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, true);
            
            // Thêm một lớp bảo vệ chống DevTools bằng cách detect console
            let devtoolsDetected = false;
            
            const checkDevTools = function() {
                const widthThreshold = window.outerWidth - window.innerWidth > 160;
                const heightThreshold = window.outerHeight - window.innerHeight > 160;
                
                if (widthThreshold || heightThreshold) {
                    if (!devtoolsDetected) {
                        devtoolsDetected = true;
                        // Có thể thực hiện hành động khi phát hiện
                        // console.clear();
                    }
                } else {
                    devtoolsDetected = false;
                }
            };
            
            setInterval(checkDevTools, 1000);
            
        })();
        </script>';
    }
}
