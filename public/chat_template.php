        <div class="form-container">
            <!-- 書き込みフォーム -->
            <form action="chat_proccess.php" method="POST" class="write-form">
                <div id="chatform">
                    <div class="input-group">
                        <label for="name">名前: </label>
                        <input type="text" id="name" name="name" required />
                    </div>
                    <div class="input-group">
                        <label for="body">本文: </label>
                        <input type="text" id="body" name="body" required />
                    </div>
                    <input type="hidden" name="write"/>
                    <button type="submit">書き込み</button>
                </div>
            </form>
            <!-- ログ削除フォーム -->
            <form class="delete-form" action="chat_proccess.php" method="POST" style="display: inline-block;">
                <input type="hidden" name="delete"/>
                <input type="hidden" name="display_count" value=$display_count/>
                <button type="submit" onclick="return confirm('本当にログを削除してもよろしいですか？')" class="delete-button">ログを削除</button>
            </form>
        </div>
        <!-- 表示変更フォーム -->
        <form class="chatform" action="chat.php" method="GET">
            <select class="custom-select" name="display_count" size="1">
                <?php
                    // 表示件数の選択肢を配列で定義
                    $display_options = [10, 20, 30, 40, 50];
                    foreach ($display_options as $value) {
                        // $display_countと一致する場合はselected属性を付与
                        $selected = ($value == $display_count) ? ' selected' : '';
                        echo "<option value=\"{$value}\"{$selected}>{$value}件ずつ表示</option>";
                    }
                ?>
            </select>
            <button type="submit">リロード</button>
        </form>
        <br/>
        <br/>
        <?php
            // チャットログの表示
            foreach ($messages as $message) {
                $name = $message['name'] . " =&gt; ";
                $body = $message['body'] . "&nbsp;&nbsp;&nbsp;";
                $date = new DateTime(htmlspecialchars($message["ctime"]));
                $ctime = $date->format('Y-m-d H:i:s');

                echo "<div class='chat'>{$name}{$body}[{$ctime}]</div>";
            }
          
            $pager = null;

            if ($offset > 0) {
                // offsetが0以上であれば前ページのリンクを表示
                $prev  = $offset - $page_max;
                $pager = "[<a href=\"chat.php?offset={$prev}&display_count={$display_count}\">&lt;- 前のページ</a>]";
            }

            if (count($messages) > $page_max) {
                // 設定数以上あれば次ページのリンクを表示
                array_pop($messages);  // $page_max + 1件取得しているため、最後のログ(レコード)のみ削除
                $next   = $offset + $page_max;
                $pager .= "[<a href=\"chat.php?offset={$next}&display_count={$display_count}\">-&gt;次のページ</a>]";
            }

            // ページャの表示
            if (isset($pager)) {
                echo "<p>$pager</p><br/>";
            }
        ?>
