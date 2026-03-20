<?php

namespace Techart\BxApp\Base\Scheme;

class BaseScheme
{
	public static $userType = '';
	public static $description = '';

	/**
	 * @return array
	 */
	public static function scheme(): array
	{
		return [
			'fields' => [
				[
					'type' => 'string',
					'code' => 'title',
					'name' => 'Заголовок',
					'required' => false
				],
				[
					'type' => 'html',
					'code' => 'description',
					'name' => 'Описание',
					'required' => false
				]
			],
		];
	}

	public static function GetUserTypeDescription(): array
	{
		return [
			'PROPERTY_TYPE' => 'S',
			'USER_TYPE' => static::$userType,
			'DESCRIPTION' => static::$description,
			'GetPropertyFieldHtml' => [static::class, 'GetPropertyFieldHtml'],
			"ConvertToDB" => [static::class, "ConvertToDB"],
			"ConvertFromDB" => [static::class, "ConvertFromDB"],
			"CheckFields" => [static::class, "CheckFields"],
		];
	}

	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		if (!method_exists(static::class, 'scheme')) {
			\Logger::error('Для кастомного свойства ' . get_called_class() . ' не заполнена схема полей.');
			return false;
		}

		$scheme = static::scheme();
		$html = '';

		if (isset($scheme['fields']) && !empty($scheme['fields'])) {
			foreach ($scheme['fields'] as $field) {
				ob_start(); ?>
				<br>
				<div style="font-size: 16px;">
					<? if ($field['required']): ?><b><? endif; ?>		
						<?= $field['name'] ?>
					<? if ($field['required']): ?></b><? endif; ?>		
				</div>
				<br>
				<?
				$html .= ob_get_contents();
				ob_end_clean();
				switch($field['type']) {
					case 'string':
						$index = 0;
						$show = true;
						ob_start();
						?>
							<table cellpadding="0" cellspacing="0" class="nopadding" width="100%" id="tb<?= md5($field['code']) ?>" >
								<? if (isset($value['VALUE'][$field['code']])): ?>
									<? foreach ($value['VALUE'][$field['code']] as $key => $val): ?>
										<? $show = false; ?>
										<tr>
											<td>
												<input type="text" size="70" name="<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>][n<?= $index ?>]" value="<?= htmlspecialchars(isset($value['VALUE'][$field['code']]) && isset($value['VALUE'][$field['code']]['n' . $index]) ? $value['VALUE'][$field['code']]['n' . $index] : '') ?>">
											</td>
										</tr>
										<? $index++; ?>
										<? if (!$field['multiple']) {
											break;
										} ?>
									<? endforeach; ?>
								<? endif; ?>
								<? if ($field['multiple'] || $show): ?>
									<tr>
										<td>
											<input type="text" size="70" name="<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>][n<?= $index ?>]">
										</td>
									</tr>
									<? $index++ ?>
								<? endif; ?>
								<? if ($field['multiple']): ?>
									<tr>
										<td>
											<input type="button" value="Добавить..." id="btnAdd<?= md5($field['code']) ?>">
											<span id="sp_<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>]_<?= 'n' . $index ?>"></span>
										</td>
									</tr>
								<? endif; ?>
							</table>
							<script>
								document.addEventListener('DOMContentLoaded', () => {
									var MV_<?= md5($field['code']) ?> = <?= $index ?>;
									document.getElementById('btnAdd<?= md5($field['code']) ?>').addEventListener('click', btnAdd<?= md5($field['code']) ?>)
									function btnAdd<?= md5($field['code']) ?>() {
										oTbl=document.getElementById('tb<?= md5($field['code']) ?>');
										oRow=oTbl.insertRow(oTbl.rows.length - 1);
										oCell=oRow.insertCell(-1);
										oCell.innerHTML='<input type="text" size="70" name="<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>][n' + MV_<?= md5($field['code']) ?> + ']">';
										oCell.innerHTML+='<span id="sp_<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>]_n' + MV_<?= md5($field['code']) ?> + '"></span>';
										MV_<?= md5($field['code']) ?>++;
									}
								})
							</script>
							<br>
						<?
						$html .= ob_get_contents();
						ob_end_clean();
						break;
					case 'text':
						$index = 0;
						$show = true;
						ob_start();
						?>
							<table cellpadding="0" cellspacing="0" class="nopadding" width="100%" id="tb<?= md5($field['code']) ?>" >
								<? if (isset($value['VALUE'][$field['code']])): ?>
									<? foreach ($value['VALUE'][$field['code']] as $key => $val): ?>
										<? $show = false; ?>
										<tr>
											<td>
												<textarea rows="5" cols="100" name="<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>][n<?= $index ?>]"><?= htmlspecialchars(isset($value['VALUE'][$field['code']]) && isset($value['VALUE'][$field['code']]['n' . $index]) ? $value['VALUE'][$field['code']]['n' . $index] : '') ?></textarea>
											</td>
										</tr>
										<? $index++; ?>
										<? if (!$field['multiple']) {
											break;
										} ?>
									<? endforeach; ?>
								<? endif; ?>
								<? if ($field['multiple'] || $show): ?>
									<tr>
										<td>
											<textarea rows="5" cols="100" name="<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>][n<?= $index ?>]"></textarea>
										</td>
									</tr>
									<? $index++ ?>
								<? endif; ?>
								<? if ($field['multiple']): ?>
									<tr>
										<td>
											<input type="button" value="Добавить..." id="btnAdd<?= md5($field['code']) ?>">
											<span id="sp_<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>]_<?= 'n' . $index ?>"></span>
										</td>
									</tr>
								<? endif; ?>
							</table>
							<script>
								document.addEventListener('DOMContentLoaded', () => {
									var MV_<?= md5($field['code']) ?> = <?= $index ?>;
									document.getElementById('btnAdd<?= md5($field['code']) ?>').addEventListener('click', btnAdd<?= md5($field['code']) ?>)
									function btnAdd<?= md5($field['code']) ?>() {
										oTbl=document.getElementById('tb<?= md5($field['code']) ?>');
										oRow=oTbl.insertRow(oTbl.rows.length - 1);
										oCell=oRow.insertCell(-1);
										oCell.innerHTML='<textarea rows="5" cols="100" name="<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>][n' + MV_<?= md5($field['code']) ?> + ']"></textarea>';
										MV_<?= md5($field['code']) ?>++;
									}
								})
							</script>
							<br>
						<?
						$html .= ob_get_contents();
						ob_end_clean();
						break;
					case 'html':
						$index = 0;
						$isHtmlField = false;
						$show = true;

						if (\Bitrix\Main\Loader::includeModule('fileman')) {
							$isHtmlField = true;
						}

						$propName = $strHTMLControlName["VALUE"] . '[' . $field['code'] . ']';
						ob_start();
						?>
							<table width="100%"> <?
								if (isset($value['VALUE'][$field['code']]) && !empty($value['VALUE'][$field['code']])) {
									foreach ($value['VALUE'][$field['code']] as $htmlValue) {
										$show = false;
										$text_name = preg_replace("/([^a-z0-9])/is", "_", $propName . '[n' . $index . '][VALUE][TEXT]');
										$text_type = preg_replace("/([^a-z0-9])/is", "_", $propName . '[n' . $index . '][VALUE][TYPE]');
										if($isHtmlField) { ?>
											<tr>
												<td colspan="2" align="center">
													<? \CFileMan::AddHTMLEditorFrame(
														$text_name,
														htmlspecialcharsBx($htmlValue['TEXT'] ?? ''),
														$text_type,
														mb_strtolower($htmlValue['TYPE'] ?? 'TEXT'),
														['height' => 200],
														"N",
														0,
														"",
														"",
														arAdditionalParams: [
															'toolbarConfig' => []
														]
													); ?>
												</td>
											</tr>
										<? } else { ?>
											<textarea name="<?= $text_name ?>"><?= isset($value['VALUE'][$field['code']][$index]['TEXT']) ? $value['VALUE'][$field['code']][$index]['TEXT'] : '' ?></textarea>
										<? }
										if (!$field['multiple']) {
											break;
										}
										$index++;
									}
								}
								if ($field['multiple'] || $show):
									for ($i = 0; $i < 3; $i++) {
										$text_name = preg_replace("/([^a-z0-9])/is", "_", $propName . '[n' . $index . '][VALUE][TEXT]');
										$text_type = preg_replace("/([^a-z0-9])/is", "_", $propName . '[n' . $index . '][VALUE][TYPE]');
										if ($isHtmlField): ?>
											<tr>
												<td colspan="2" align="center">
													<? \CFileMan::AddHTMLEditorFrame(
														$text_name,
														'',
														$text_type,
														'text',
														['height' => 200],
														"N",
														0,
														"",
														"",
														arAdditionalParams: [
															'toolbarConfig' => []
														]
													); ?>
												</td>
											</tr>
										<? else: ?>
											<tr>
												<td>
													<textarea rows="5" cols="100" name="<?= $text_name ?>"></textarea>
												</td>
											</tr>
										<? endif;
										$index++;
									} ?>
								<? endif; ?>
								<br>
							</table>
						<?
						$html .= ob_get_contents();
						ob_end_clean();
						break;
					case 'file':
						$files = [];
						if ($field['multiple'] && !empty($value['VALUE'][$field['code']])) {
							foreach ($value['VALUE'][$field['code']] as $file) {
								$files[$strHTMLControlName['VALUE'] . '[' . $field['code'] . '][' . count($files) . ']'] = $file;
							}
						}
						ob_start();
						?>
						<?= \Bitrix\Main\UI\FileInput::createInstance(
							array(
								"name" => $strHTMLControlName["VALUE"] . '[' . $field['code'] . ']' . ($field['multiple'] ? '[n#IND#]' : ''),
								"id" => $strHTMLControlName["VALUE"] . '[' . $field['code'] . ']_'.mt_rand(1, 1000000),
								"description" => 'N',
								"allowUpload" => "F",
								"allowUploadExt" => '',
								"maxCount" => $field['multiple'] ? 0 : 1,
								"upload" => true,
								"medialib" => false,
								"fileDialog" => false,
								"cloud" => false
							)
						)->show(!empty($files) ? $files : 0, false); ?>
						<br>
						<?
						$html .= ob_get_contents();
						ob_end_clean();
						break;
					case 'checkbox':
						ob_start();
						?>
						<table cellpadding="0" cellspacing="0" class="nopadding" width="100%" id="tb<?= md5($field['code']) ?>" >
							<tr>
								<td>
									<input type="checkbox" id="" name="<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>]" <? if ($value['VALUE'][$field['code']] ?? false): ?> checked <? endif; ?>>
									<span><?= $field['name'] ?></span>
								</td>
							</tr>
						</table>
						<br>
						<?
						$html .= ob_get_contents();
						ob_end_clean();
						break;
					case 'link_elements':
						$show = true;
						$index = 0;
						$nameElem = $strHTMLControlName['VALUE'] . '[' . $field['code'] . ']';
						$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");
						$windowTableId = 'iblockprop-'.\Bitrix\Iblock\PropertyTable::TYPE_ELEMENT.'-'.$arProperty['ID'].'-'.\App::model($field['model'])->getInfoblock()['ID'];
						ob_start();
						?>
						<table cellpadding="0" cellspacing="0" class="nopadding" width="100%" id="tb<?= md5($nameElem) ?>" >
							<?if (isset($value['VALUE'][$field['code']])): ?>
								<? foreach ($value['VALUE'][$field['code']] as $key => $val): ?>
									<? $show = false; ?>
									<?php
										$db_res = \CIBlockElement::GetByID($val);
										$ar_res = $db_res->GetNext();
									?>
									<tr>
										<td>
											<input type="text" name="<?= $nameElem ?>[<?= 'n' . $index ?>]" id="<?= $nameElem ?>[<?= 'n' . $index ?>]" value="<?= htmlspecialcharsbx($val) ?>">
											<input type="button" value="..." onClick="jsUtils.OpenWindow('<?= $selfFolderUrl ?>iblock_element_search.php?lang=<?= TBA_LANGUAGE_ID ?>&amp;IBLOCK_ID=<?= \App::model($field['model'])->getInfoblock()['ID'] ?>&amp;n=<?= $nameElem ?>&amp;k=<?= 'n' . $index ?>&amp;iblockfix=y&amp;tableId=<?= $windowTableId ?>', 900, 700);">
											<span id="sp_<?= md5($nameElem) ?>_<?= 'n' . $index ?>"><?= $ar_res['NAME'] ?? '' ?></span>
										</td>
									</tr>
									<? $index++; ?>
									<? if (!$field['multiple']) {
										break;
									} ?>
								<? endforeach; ?>
							<? endif; ?>
							<? if ($field['multiple'] || $show): ?>
							<tr>
								<td>
									<input type="text" name="<?= $nameElem ?>[<?= 'n' . $index ?>]" id="<?= $nameElem ?>[<?= 'n' . $index ?>]" value="<?= htmlspecialcharsbx("") ?>">
									<input type="button" value="..." onClick="jsUtils.OpenWindow('<?= $selfFolderUrl ?>iblock_element_search.php?lang=<?= TBA_LANGUAGE_ID ?>&amp;IBLOCK_ID=<?= \App::model($field['model'])->getInfoblock()['ID'] ?>&amp;n=<?= $nameElem ?>&amp;k=<?= 'n' . $index ?>&amp;iblockfix=y&amp;tableId=<?= $windowTableId ?>', 900, 700);">
									<span id="sp_<?= md5($nameElem) ?>_<?= 'n' . $index ?>"></span>
								</td>
							</tr>
							<? endif ?>
							<? if ($field['multiple']): ?>
								<tr>
									<td>
										<input type="button" value="Добавить..." onClick="jsUtils.OpenWindow('<?= $selfFolderUrl ?>iblock_element_search.php?lang=<?= TBA_LANGUAGE_ID ?>&amp;IBLOCK_ID=<?= \App::model($field['model'])->getInfoblock()['ID'] ?>&amp;n=<?= $nameElem ?>&amp;m=y&amp;k=<?= 'n' . $index ?>&amp;iblockfix=y&amp;tableId=<?= $windowTableId ?>', 900, 700);">
										<span id="sp_<?= md5($nameElem) ?>_<?= 'n' . $index ?>"></span>
									</td>
								</tr>
								<? $index++; ?>
							<? endif; ?>
						</table>
						<script>
							var MV_<?= md5($nameElem) ?> = <?= $index ?>;
							function InS<?= md5($nameElem) ?>(id, name) {
								oTbl=document.getElementById('tb<?= md5($nameElem) ?>');
								oRow=oTbl.insertRow(oTbl.rows.length - 1);
								oCell=oRow.insertCell(-1);
								oCell.innerHTML='<input type="text" name="<?= $nameElem ?>[n' + MV_<?= md5($nameElem) ?> + ']" id="<?= $nameElem ?>[n' + MV_<?= md5($nameElem) ?> + ']" value="' + id + '">';
								oCell.innerHTML+='<input type="button" value="..." onClick="jsUtils.OpenWindow(\'<?= $selfFolderUrl ?>iblock_element_search.php?lang=<?= TBA_LANGUAGE_ID ?>&amp;IBLOCK_ID=<?= \App::model($field['model'])->getInfoblock()['ID'] ?>&amp;n=<?= $nameElem ?>&amp;k=n' + MV_<?= md5($nameElem) ?> + '&amp;iblockfix=y&amp;tableId=<?= $windowTableId ?>\', 900, 700);">';
								oCell.innerHTML+='<span id="sp_<?= md5($nameElem) ?>_n' + MV_<?= md5($nameElem) ?> + '">' + name + '</span>';
								MV_<?= md5($nameElem) ?>++;
							}
						</script>
						<br>
						<?
						$html .= ob_get_contents();
						ob_end_clean();
						break;
					case 'link_highload':
						$items = [];

						if (isset($field['model']) && !empty($field['model'])) {
							try {
								$elements = \App::model($field['model'])->getElements(['ID', 'UF_NAME', 'UF_XML_ID'], ['!UF_XML_ID' => false]);

								while ($element = $elements->fetch()) {
									$items[] = [
										'id' => $element['ID'],
										'name' => $element['UF_NAME'],
										'value' => $element['UF_XML_ID']
									];
								}
							} catch (\Exception $e) {
								\Logger::error('BaseScheme: ' . $e->getMessage());
								continue;
							}
						}

						ob_start();
						?>
							<select name="<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>][]"<? if ($field['multiple']): ?> multiple<? endif; ?>>
								<option value=""<? if(!isset($value['VALUE'][$field['code']])): ?> selected<? endif; ?>>(не установлено)</option>
								<? foreach ($items as $item): ?>
									<option value="<?= $item['value'] ?>"<? if(isset($value['VALUE'][$field['code']]) && is_array($value['VALUE'][$field['code']]) && in_array($item['value'], $value['VALUE'][$field['code']])): ?> selected<? endif; ?>><?= $item['name'] ?> [<?= $item['id'] ?>]</option>
								<? endforeach; ?>
							</select>
						<?
						$html .= ob_get_contents();
						ob_end_clean();
						break;
					case 'list':
						ob_start();
						?>
							<select name="<?= $strHTMLControlName['VALUE'] ?>[<?= $field['code'] ?>][]"<? if ($field['multiple']): ?> multiple<? endif; ?>>
								<option value=""<? if(!isset($value['VALUE'][$field['code']])): ?> selected<? endif; ?>>(не установлено)</option>
								<? if (isset($field['list']) && is_array($field['list'])): ?>
									<? foreach ($field['list'] as $item): ?>
										<option value="<?= $item['value'] ?>"<? if(isset($value['VALUE'][$field['code']]) && is_array($value['VALUE'][$field['code']]) && in_array($item['value'], $value['VALUE'][$field['code']])): ?> selected<? endif; ?>><?= $item['title'] ?></option>
									<? endforeach; ?>
								<? endif; ?>
							</select>
						<?
						$html .= ob_get_contents();
						ob_end_clean();
						break;
					default:
						ob_start();
						?>
						<div style="color: red;">Задан неверный тип: <?= $field['type'] ?></div>
						<?
						$html .= ob_get_contents();
						ob_end_clean();
						break;
				}

				ob_start();
				if (isset($field['note']) && !empty($field['note'])) { ?>
					<p style="color: gray;"><?= $field['note'] ?></p>
				<? }
				$html .= ob_get_contents();
				ob_end_clean();
			}
		}

		return $html;
	}

	public static function ConvertToDB($arProperty, $value)
	{
		$arProp = \CIBlockElement::GetProperty($arProperty['IBLOCK_ID'], $arProperty['ELEMENT_ID'])->GetNext();

		if (!empty($value['VALUE'])) {
			$value['VALUE'] = array_filter($value['VALUE'], fn($el) => $el && ((!is_array($el) && !empty($el)) || array_filter($el, fn($e) => !empty($e))));
		}

		if (is_array($value["VALUE"])) {
			if (empty($value['VALUE'])) {
				$value['VALUE'] = null;
				return $value;
			}

			$scheme = static::scheme();

			foreach ($value['VALUE'] as $key => $data) {
				$index = array_search($key, array_column($scheme['fields'], 'code'));

				if ($index !== false) {
					$field = $scheme['fields'][$index];
	
					if ($field['type'] === 'checkbox') {
						$value['VALUE'][$key] = $value['VALUE'][$key] === 'on';
					}

					if ($field['type'] === 'file') {
						if ($field['multiple']) {
							$value['VALUE'][$key] = [];
							if (is_array($data) && count($data) > 0) {
								foreach ($data as $keyFile => $file) {
									if (is_array($file) && isset($file['tmp_name'])) {
										$newData = \CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'] . 'upload/tmp' . $file['tmp_name']);
										$file['tmp_name'] = $newData['tmp_name'];
										$id = \CFile::SaveFile($file, 'files');
										$value['VALUE'][$key][] = intval($id);
									} else {
										if (isset($_REQUEST['PROP_del']) && isset($_REQUEST['PROP_del'][$arProp['ID']][$arProp['PROPERTY_VALUE_ID']]['VALUE'][$field['code']]) &&
										$_REQUEST['PROP_del'][$arProp['ID']][$arProp['PROPERTY_VALUE_ID']]['VALUE'][$field['code']][$keyFile] === 'Y') {
											\CFile::Delete($data[$keyFile]);
											unset($data[$keyFile]);
											continue;
										}

										$value['VALUE'][$key][] = intval($file);
									}
								}
							}
						} else {
							if (isset($_REQUEST['PROP_del']) && isset($_REQUEST['PROP_del'][$arProp['ID']][$arProp['PROPERTY_VALUE_ID']]['VALUE'][$field['code']]) &&
							$_REQUEST['PROP_del'][$arProp['ID']][$arProp['PROPERTY_VALUE_ID']]['VALUE'][$field['code']] === 'Y') {
								\CFile::Delete($data);
								$value['VALUE'][$key] = null;
							} elseif (isset($data['tmp_name'])) {
								$newData = \CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'] . 'upload/tmp' . $data['tmp_name']);
								$data['tmp_name'] = $newData['tmp_name'];
								$id = \CFile::SaveFile($data, 'files');
								$value['VALUE'][$key] = intval($id);
							}
						}
					}

					if (in_array($field['type'], ['link_elements', 'string', 'text'])) {
						if ($field['type'] === 'link_elements') {
							$data = array_unique($data);
						}

						$value['VALUE'][$key] = [];
						$data = array_filter($data, fn($el) => !empty($el));

						foreach ($data as $i => $val) {
							$value['VALUE'][$key]['n' . count($value['VALUE'][$key])] = $val;
						}

						if (isset($value['VALUE'][$key]) && empty($value['VALUE'][$key])) {
							unset($value['VALUE'][$key]);
						}
					}

					if (in_array($field['type'], ['link_highload', 'list'])) {
						$value['VALUE'][$key] = array_values(array_filter($data, fn($el) => !empty($el)));

						if (isset($value['VALUE'][$key]) && empty($value['VALUE'][$key])) {
							unset($value['VALUE'][$key]);
						}
					}

					if ($field['type'] === 'html') {
						$value['VALUE'][$field['code']] = [];
						$propName = 'PROP_' . $arProp['ID'] . '__' . $arProp['PROPERTY_VALUE_ID'] . '__VALUE__' . $field['code'];
						$index = 0;
						while (isset($_REQUEST[$propName . '__n' . $index . '__VALUE__TEXT_']) && !empty($_REQUEST[$propName . '__n' . $index . '__VALUE__TEXT_'])) {
							$value['VALUE'][$field['code']][] = [
								'TEXT' => $_REQUEST[$propName . '__n' . $index . '__VALUE__TEXT_'],
								'TYPE' => $_REQUEST[$propName . '__n' . $index . '__VALUE__TYPE_'] ?? 'TEXT'
							];

							$index++;
						}

						for ($i = $index; $i < $index + 3; $i++) {
							if (isset($_REQUEST[$propName . '__n' . $i . '__VALUE__TEXT_']) && !empty($_REQUEST[$propName . '__n' . $i . '__VALUE__TEXT_'])) {
								$value['VALUE'][$field['code']][] = [
									'TEXT' => $_REQUEST[$propName . '__n' . $i . '__VALUE__TEXT_'],
									'TYPE' => $_REQUEST[$propName . '__n' . $i . '__VALUE__TYPE_'] ?? 'TEXT'
								];
							}
						}

						if (empty($value['VALUE'][$field['code']])) {
							$value['VALUE'][$field['code']] = null;
						}
					}
				}
			}

			foreach ($scheme['fields'] as $field) {
				if (!isset($value['VALUE'][$field['code']])) {
					if ($field['type'] === 'checkbox') {
						$value['VALUE'][$field['code']] = false;
					} elseif ($field['type'] === 'html') {
						$value['VALUE'][$field['code']] = [];
						$propName = 'PROP_' . $arProp['ID'] . '__' . $arProp['PROPERTY_VALUE_ID'] . '__VALUE__' . $field['code'];
						$index = 0;
						while (isset($_REQUEST[$propName . '__n' . $index . '__VALUE__TEXT_']) && !empty($_REQUEST[$propName . '__n' . $index . '__VALUE__TEXT_'])) {
							$value['VALUE'][$field['code']][] = [
								'TEXT' => $_REQUEST[$propName . '__n' . $index . '__VALUE__TEXT_'],
								'TYPE' => $_REQUEST[$propName . '__n' . $index . '__VALUE__TYPE_'] ?? 'TEXT'
							];

							$index++;
						}

						for ($i = $index; $i < $index + 3; $i++) {
							if (isset($_REQUEST[$propName . '__n' . $i . '__VALUE__TEXT_']) && !empty($_REQUEST[$propName . '__n' . $i . '__VALUE__TEXT_'])) {
								$value['VALUE'][$field['code']][] = [
									'TEXT' => $_REQUEST[$propName . '__n' . $i . '__VALUE__TEXT_'],
									'TYPE' => $_REQUEST[$propName . '__n' . $i . '__VALUE__TYPE_'] ?? 'TEXT'
								];
							}
						}

						if (empty($value['VALUE'][$field['code']])) {
							$value['VALUE'][$field['code']] = null;
						}
					} else {
						$value['VALUE'][$field['code']] = null;
					}
				}
			}

			$value["VALUE"] = json_encode($value["VALUE"]);
		}

		return $value;
	}

	public static function ConvertFromDB($arProperty, $value)
	{
		$value["VALUE"] = json_decode($value["VALUE"], true);

		return $value;
	}

	public static function CheckFields($arProperty, $value)
	{
		$errors = [];

		if (is_array($value['VALUE'])) {
			$value['VALUE'] = array_filter($value['VALUE'], fn($el) => $el && ((!is_array($el) && !empty($el)) || array_filter($el, fn($e) => !empty($e))));
		}
		
		if (!empty($value['VALUE'])) {
			$scheme = static::scheme();

			foreach ($scheme['fields'] as $field) {
				if (isset($field['required']) && $field['required']) {
					if (isset($value['VALUE'][$field['code']]) || $field['type'] === 'html') {
						if (in_array($field['type'], ['link_elements', 'string', 'text'])) {
							$value['VALUE'][$field['code']] = array_filter($value['VALUE'][$field['code']], fn($el) => !empty($el));
	
							if (empty($value['VALUE'][$field['code']])) {
								$errors[$field['code']] = 'Поле "' . $field['name'] . '" внутри свойства "' . $arProperty['NAME'] . '" является обязательным!';
							}
						}
	
						if (in_array($field['type'], ['link_highload', 'list'])) {
							$value['VALUE'][$field['code']] = array_filter($value['VALUE'][$field['code']], fn($el) => !empty($el));

							if (empty($value['VALUE'][$field['code']])) {
								$errors[$field['code']] = 'Поле "' . $field['name'] . '" внутри свойства "' . $arProperty['NAME'] . '" является обязательным!';
							}
						}

						if ($field['type'] === 'file') {
							$propId = array_key_first($_REQUEST['PROP'][$arProperty['ID']]);

							if (!$field['multiple']) {
								if (isset($_REQUEST['PROP_del']) && isset($_REQUEST['PROP_del'][$arProperty['ID']][$propId]['VALUE'][$field['code']]) &&
								$_REQUEST['PROP_del'][$arProperty['ID']][$propId]['VALUE'][$field['code']] === 'Y') {
									$errors[$field['code']] = 'Поле "' . $field['name'] . '" внутри свойства "' . $arProperty['NAME'] . '" является обязательным!';
								}
							}
						}
					} else {
						$errors[$field['code']] = 'Поле "' . $field['name'] . '" внутри свойства "' . $arProperty['NAME'] . '" является обязательным!';
					}
				}
			}
		}

		return $errors;
	}
}