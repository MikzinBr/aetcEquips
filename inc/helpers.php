<?php

function format_euro(int $centavos): string
{
  return number_format($centavos / 100, 2, ',', '.') . ' €';
}

function limitText($text, $maxLength)
{
  if (mb_strlen($text, 'UTF-8') <= $maxLength) {
    return $text;
  }
  return mb_substr($text, 0, $maxLength, 'UTF-8') . '...';
}
