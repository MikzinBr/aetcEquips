<?php

function format_euro(int $centavos): string
{
  return number_format($centavos / 100, 2, ',', '.') . ' €';
}
