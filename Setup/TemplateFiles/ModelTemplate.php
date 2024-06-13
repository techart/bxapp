
<?php
class {{model_name}} extends BaseIblockModel
{
	public $table = '{{model_table}}';
	public $iblockElementsSelect = ['ID', 'NAME'];


	public function getElementsData(): array
	{
		$data = [];

		if ($this->hasCache()) {
			$data = $this->getCache();
		} elseif ($this->startCache()) {
			$items = $this->getElements();

			while ($item = $items->fetch()) {
				$data[] = [
					'id' => $item['ID'],
					'name' => $item['NAME'],
				];
			}

			$this->endCache($data);
		}

		return $this->result('', '', $data);
	}
}
