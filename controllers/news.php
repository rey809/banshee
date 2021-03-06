<?php
	class news_controller extends Banshee\controller {
		public function execute() {
			$this->view->description = "News";
			$this->view->keywords = "news";
			$this->view->title = "News";
			$this->view->add_alternate("News", "application/rss+xml", "/news.xml");

			if ($this->page->type == "xml") {
				/* RSS feed
				 */
				$rss = new Banshee\RSS($this->view);
				if ($rss->fetch_from_cache("news_rss") == false) {
					$rss->title = $this->settings->head_title." news";
					$rss->description = $this->settings->head_description;

					if (($news = $this->model->get_news(0, $this->settings->news_rss_page_size)) != false) {
						foreach ($news as $item) {
							$link = "/news/".$item["id"];
							$rss->add_item($item["title"], $item["content"], $link, $item["timestamp"]);
						}
					}
					$rss->to_output();
				}
			} else if (valid_input($this->page->pathinfo[1], VALIDATE_NUMBERS, VALIDATE_NONEMPTY)) {
				/* News item
				 */
				if (($item = $this->model->get_news_item($this->page->pathinfo[1])) == false) {
					$this->view->add_tag("result", "Unknown news item");
				} else {
					$this->view->title = $item["title"]." - News";
					$item["timestamp"] = date("j F Y, H:i", strtotime($item["timestamp"]));
					$this->view->record($item, "news");
				}
			} else {
				/* News overview
				 */
				if (($count = $this->model->count_news()) === false) {
					$this->view->add_tag("result", "Database error");
					return;
				}

				$paging = new Banshee\pagination($this->view, "news", $this->settings->news_page_size, $count);

				if (($news = $this->model->get_news($paging->offset, $paging->size)) === false) {
					$this->view->add_tag("result", "Database error");
					return;
				}

				foreach ($news as $item) {
					$item["timestamp"] = date("j F Y, H:i", $item["timestamp"]);
					$this->view->record($item, "news");
				}

				$paging->show_browse_links(7, 3);
			}
		}
	}
?>
