<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockDevToolsAdmin
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
                
                // Cho phép các element có class allow-copy
                const allowedClasses = [
                    "allow-copy",
                    "form-control",
                    "form-select",
                    "ckeditor",
                    "cke",
                    "cke_contents",
                    "cke_editable"
                ];
                
                for (let className of allowedClasses) {
                    if (target.classList && target.classList.contains(className)) {
                        return true;
                    }
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
            
            // Chặn DevTools - Chỉ chặn các phím tắt cơ bản
            document.addEventListener("keydown", function(e) {
                // F12
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
                
                // Ctrl + Shift + C (Inspect Element) - Cho phép trong element được phép
                if (e.ctrlKey && e.shiftKey && (e.key === "C" || e.keyCode === 67)) {
                    if (!isAllowedElement(e.target)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }

                if (e.ctrlKey && (e.key === "p" || e.key === "P" || e.keyCode === 80)) {
                    // Cho phép trong input/textarea để tìm kiếm
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
                
                // Ctrl + U (View Source)
                if (e.ctrlKey && (e.key === "u" || e.key === "U" || e.keyCode === 85)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
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
                
            }, true);
            
            // Chặn chuột phải - Cho phép trong element được phép
            document.addEventListener("contextmenu", function(e) {
                if (!isAllowedElement(e.target)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
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
                }
                
                input, textarea, select, 
                [contenteditable="true"],
                .allow-copy,
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
                }
                
                [id*="cke_"], [id*="cke_"] * {
                    -webkit-user-select: text !important;
                    -moz-user-select: text !important;
                    -ms-user-select: text !important;
                    user-select: text !important;
                }
            `;
            document.head.appendChild(style);
            
            // Chặn console
            const noop = function() {};
            const methods = ["log", "debug", "info", "warn", "error"];
            for (let i = 0; i < methods.length; i++) {
                console[methods[i]] = noop;
            }
            
        })();
        </script>';
    }
}
