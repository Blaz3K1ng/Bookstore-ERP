<?php
$url="postgresql://neondb_owner:npg_iwQhaUR7tVu3@ep-young-water-ao25ofwu-pooler.c-2.ap-southeast-1.aws.neon.tech/neondb?sslmode=require";
$url=preg_replace("#^postgresql://#","postgres://",$url);
$parts=parse_url($url);
$query=$parts["query"] ?? "";
parse_str($query,$q);
unset($q["channel_binding"]);
$newQuery=http_build_query($q);
$scheme=$parts["scheme"] ?? "";
$user=$parts["user"] ?? "";
$pass=$parts["pass"] ?? "";
$auth=$user!=="" ? $user . ($pass!=="" ? ":" . $pass : "") . "@" : "";
$host=$parts["host"] ?? "";
$port=isset($parts["port"]) ? ":" . $parts["port"] : "";
$path=$parts["path"] ?? "";
$frag=isset($parts["fragment"]) ? "#" . $parts["fragment"] : "";
echo $scheme . "://" . $auth . $host . $port . $path . ($newQuery!=="" ? "?" . $newQuery : "") . $frag;
