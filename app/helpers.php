<?php

if (!function_exists('smartBack')) {
    function smartBack(string $defaultRoute, $params = []): string
    {
        if (!is_array($params)) {
            $params = [$params];
        }

        $prev = url()->previous();
        $curr = url()->current();

        if (!$prev) {
            return route($defaultRoute, $params);
        }

        $prevPath = parse_url($prev, PHP_URL_PATH) ?: $prev;
        $currPath = parse_url($curr, PHP_URL_PATH) ?: $curr;

        $prevBase = preg_replace('#^/[a-z]{2}(/|$)#', '/', $prevPath);
        $currBase = preg_replace('#^/[a-z]{2}(/|$)#', '/', $currPath);

        if ($prevBase === $currBase) {
            return route($defaultRoute, $params);
        }

        $escaped = preg_quote(rtrim($currBase, '/'), '#');
        if (preg_match('#^' . $escaped . '/edit($|/|\?)#', $prevBase)) {
            return route($defaultRoute, $params);
        }

        return $prev;
    }
}
