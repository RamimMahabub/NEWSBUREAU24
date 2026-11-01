<?php

namespace App\Support;

class CleanHtml
{
    /** Deliberate allowlist: editor markup may never contain active content. */
    public static function sanitize(string $html): string
    {
        if ($html === '') {
            return '';
        }
        $dom = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $allowed = ['div', 'p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'a', 'hr', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'figure', 'figcaption', 'img'];
        foreach (iterator_to_array($dom->getElementsByTagName('*')) as $node) {
            if (! in_array($node->nodeName, $allowed, true)) {
                $node->parentNode?->removeChild($node);

                continue;
            }
            foreach (iterator_to_array($node->attributes) as $attribute) {
                $name = $attribute->name;
                $value = trim($attribute->value);
                $safe = in_array($name, ['alt', 'title', 'colspan', 'rowspan'], true)
                    || ($name === 'href' && preg_match('~^(https?://|mailto:|/(?!/)|#)~i', $value))
                    || ($name === 'src' && preg_match('~^/media/\d+$~', $value));
                if (! $safe) {
                    $node->removeAttribute($name);
                }
            }
        }
        $result = $dom->saveHTML($dom->documentElement) ?: '';

        return $result;
    }
}
