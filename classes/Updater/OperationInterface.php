<?php

namespace AccelaSearch\Updater;

interface Operation
{
  public function generateQueries(UpdateRow $update_row, UpdateContext $context);
  public function getQueries(): string;
}
