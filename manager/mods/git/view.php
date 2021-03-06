<?php

class gitActionView {

	/**
	 * рендеринг страницы ошибки
	 * 
	 * @param type $text
	 */
	public function renderError($text) {
		$resText = $text;
		if(is_array($text)){
			if(isset($text['error']) AND isset($text['error']['message'])){
				$resText = $text['error']['message'];
			}
		}
		myOutput::out($resText);
	}

	/**
	 * рендеринг главной страницы веток и операций
	 * 
	 * @param type $res
	 */
	public function renderIndexPage($res) {
		$html = '';
		if (is_array($res)) {
			
			if(isset($res['error'])){
				$this->renderError($res);
				exit;
			}

			$repsHtml = '<div class="gitReps">';
			// список доступных репов
			// если один
			if (count($res['reps']) == 1) {
				$currRep = current($res['reps']);
				$repsHtml .= '
					<div class="block">
						repository: <b>' . $currRep['name'] . '</b>
					</div>';
			}
			// если много
			else {
				$repsHtml .= '
					<div class="block">
						<a class="button js_getUserFeature" href="#" title="">Взять фичу</a>
						<a class="button js_acceptUserFeature" href="#" title="">Принять задачу</a>
						current repository is
					<select name="rep" class="js_repsChng">';
				foreach ($res['reps'] as $currRep) {
					$name = $currRep['name'];
					$path = $currRep['path'];
					$active = isset($currRep['active']) ? ' selected ' : '';
					$repsHtml .= '<option value="' . $path . '" '.$active.'>' . $name . '</option>';
				}
				$repsHtml .= '</select></div>';
			}
			$repsHtml .= '
					<div class="block">
						<a class="button" href="?" title="обновить текущее состояние">refresh</a>
					</div>';
			$repsHtml .= '</div>';



			$current = false;

			// перебор имеющихся веток
			foreach ($res['branches'] as $_i => $_v) {

				// проверяем наличие в массиве списка индексов и превращаем или в $текст или в $$переменную
				// на выходе будут PHP-переменные $local и $remote
				$indexArr = array('local', 'remote');
				foreach ($indexArr as $_f) {
					$$_f = isset($_v[$_f]) ? $_i : '';
					$$_f = '<input type="text" readonly value="' . $$_f . '">';
					if (isset($_v[$_f]) AND $_v[$_f] > 1) {
						$$_f .= ' <a href="https://redmine.suffra.com/issues/' . $_v[$_f] . '" target="_blank"> redmine&rarr;</a>';
					}
					$modI = $_f . '_i';
					$$modI = $$_f == '' ? '' : $_i;
				}

				/** @var $local string */
				/** @var $remote string */
				/** @var $local_i string */
				/** @var $remote_i string */

				if (!$current && isset($_v['current'])){
					$current = $_i;
					$local = '<span class="currentBranch">'.$local.'<span>';
				}

				$html .= '
					<tr>
						<td>
							<a class="button showOnHover isRelative withPopup js_showBranchActionsPopup" href="#" data-type="local" data-name="' . $local_i . '">+</a> 
								|
							' . $local . '
						</td>
						<td>&nbsp;</td>
						<td>
							<a class="button showOnHover isRelative withPopup js_showBranchActionsPopup" href="#" data-type="remote" data-name="' . $remote_i . '">+</a> 
								|
							' . $remote . '
						</td>
					</tr>
				';
			}

			$currentHtml = '<i>cant parse name of current branch :(</i>';
			if ($current) {
				$currentHtml = '
					<a class="button withPopup isRelative etcMenuButton" href="#">
						<ul class="popupMenu bigPopupMenu">
							<li class="js_git_add_branch" title="сделать checkout -b {имя_ветки}">new branch</li>
							<li class="menuDelimeter">&nbsp;</li>
							<li class="js_magicButton" data-method="git_log" data-name="-" title="посмотреть последние коммиты">git log</li>
							<li class="menuDelimeter">&nbsp;</li>
							<li class="js_magicButton" data-method="stash_save" data-name="-" title="сохранить черновую работу">stash save</li>
							<li class="js_magicButton" data-method="stash_pop" data-name="-" title="наложить последнюю отложенную">stash pop</li>
							<li class="js_magicButton" data-method="stash_list" data-name="-" title="просмотреть есть ли что отложенное">stash list</li>
							<li class="js_magicButton" data-method="stash_clear" data-name="-" title="стереть все отложенное">stash clear</li>
							<li class="menuDelimeter">&nbsp;</li>
							<li class="js_magicButton" data-method="chk_chown" data-name="-" title="проверить владельца на всех файлах"> <i>chk_chown</i> </li>
						</ul>
					</a> |
					<a class="button js_git_commit" href="#" title="сделать commit -am {коммент}">commit</a> |
					<a class="button js_magicButton" data-method="push_self" data-name="' . $current . '" href="#" title="сделать pull origin {ветка}, потом push">pull&push</a> |
					<!-- a class="button js_git_add_branch" href="#" title="сделать checkout -b {имя_ветки}">new</a> | -->
					on branch: <strong>' . $current . '</strong>
				';
			}

			$_s = '';
			foreach ($res['status'] as $_i => $_v) {
				$_s .= $_v . '<br>';
			}

			$status = '
			' . $repsHtml . '
			<div class="spacer">
				<div class="scrollerXY" style="height:220px;">
					<div class="asConsole js_myConsole">
						' . $_s . '
					</div>
				</div>
			</div>
			';

			$html = '
				<div class="currFicha spacer">' . $currentHtml . '</div>
				' . $status . '
				<div class="spacer">
					<a class="button js_show_loading" href="' . myRoute::getRoute('git', 'update_remotes') . '">
						git remote update, git remote prune origin
					</a>
				</div>
				<table class="mainTable">
					<tr>
						<th>local branches</th>
						<th width="70px">&nbsp;</th>
						<th>remotes branches</th>
					</tr>
					' . $html . '
				</table>
			';
		}

		myOutput::addCSS('git_main.css');
		myOutput::addJS('git_main.js');
		myOutput::outFullHtml($html, 'MyGit / RomanSh');
	}

}
