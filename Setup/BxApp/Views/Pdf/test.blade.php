<!DOCTYPE html>
<html>
<head>
	<title>{{ $title }}</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<style>
		@page {
			margin: 0cm 0cm;
		}

		footer {
			position: fixed;
			bottom: 0;
			width: 700px;
		}
		.page_break { page-break-after: always; }

	</style>
	<div style="width: 100%; max-width: 700px; margin: auto;">
		<div style="width: 700px; margin: auto;">
			<table width="100%" style="padding-top: 30px; padding-bottom: 20px; border-bottom: 2px solid #4D897C;">
				<tr>
					<td style="text-align: left">
						Это шапка страницы
					</td>
				</tr>
			</table>

			<table width="100%" style="margin: 30px 0 20px 0;">
				<tr>
					<td style="font-weight: bold; font-size: 25px; line-height: 100%; color: #4D897C;">
						{{ $title }}
					</td>
				</tr>
				<tr>
					<td>
						<p>Контент страницы</p>
						<img src="test.png" width="100" height="77" />
						<img src="{{ $host }}/test.png" width="100" height="77" />
						<img src="{{ $host }}test.jpg" width="100" height="77" />
						<img src="{{ $host }}test.jpg" width="100" height="77" />
						<img src="/var/www/landcomp/landcomp.ru/www/test.jpg" width="100" height="77" />
						<img src="/var/www/landcomp/landcomp.ru/www/test.png" width="100" height="77" />
						<img src="http://landcomp.ru.zabrodskij.techart.intranet/test.png" width="100" height="77" />
						<img src="https://vitastatic.techart.ru/files/service_results/0011/5920/result_figure-5920-1688041307.jpg" width="100" height="77" />
					</td>

				</tr>
			</table>
			<div class="page_break"></div>

			<p>Контент страницы</p>
			<footer name="footer">
				<table width="100%" style="border-top: 2px solid #4D897C; margin-top: 50px;">
					<tr>
						<td style="text-align: left; font-style: normal; font-weight: 400; font-size: 12px; line-height: 120%; color: #424242; padding: 20px 0 20px 0;">
							Дата выгрузки: {!! date('d.m.Y') !!}
						</td>
					</tr>
				</table>
			</footer>
		</div>
	</div>
</body>
</html>
