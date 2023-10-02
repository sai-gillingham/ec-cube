<?php


namespace Plugin;

use AcceptanceTester;
use Codeception\Util\Fixtures;
use Eccube\Common\EccubeConfig;

/**
 * @group plugin_verify
 */
class PluginVerifyCest
{
    /** @var EccubeConfig */
    private $config;
    /** プラグインコード */
    private $plugin_code;

    public function _before(AcceptanceTester $I){
        if(!file_exists(__DIR__ . "/../../../var/cache/dev/htmlpurifier")){
            mkdir(__DIR__ . "/../../../var/cache/dev/htmlpurifier", 775);
        }
        exec("php bin/console cache:clear --no-warmup");
        $this->config = Fixtures::get('test_config');
        $I->loginAsAdmin();
    }
    public function 認証キーを入力(AcceptanceTester $I)
    {
        $I->amOnPage('/admin/store/plugin/authentication_setting');
        $config = Fixtures::get('test_config');
        $I->fillField('/html/body/div[1]/div[3]/form/div[1]/div/div[2]/div/div[2]/div[2]/div[2]/input', $config['ECCUBE_PLUGIN_VERIFY_KEY']);
        $I->click("/html/body/div[1]/div[3]/form/div[2]/div/div/div[2]/div/div/button");
        $I->wait(5);
        $I->see('保存しました');
    }
    public function プラグイン_インストール(AcceptanceTester $I){
        $I->amOnPage("/admin/store/plugin");
        $config = Fixtures::get('test_config');
        $pluginName = $config['ECCUBE_PLUGIN_VERIFY_NAME'];
        $I->see($pluginName);
        $span = '//*[@id="page_admin_store_plugin"]/div[1]/div[3]/div[2]/div/div/div[1]/div[2]/table/tbody/tr/td[1]/div/span[text() =  "'.$pluginName.'"]';
        $tr = $span . '/ancestor::tr';
        $this->plugin_code= $I->grabTextFrom($tr . "/td[3]/p");
        echo("プラグインコード:" . $this->plugin_code . "\n");
        $button = $tr . '/td[5]/a';
        $I->click($button);
        $I->wait(3);
        $I->see("インストール確認");
        $I->click("/html/body/div[1]/div[3]/div[2]/div/div/div/div[2]/div[2]/div/button[2]");
        $I->wait(1);
        $I->click("/html/body/div[1]/div[3]/div[3]/div/div/div[3]/button[2]");
        //インストール処理が終わるまで待機
        $I->waitForJS("return $.active == 0;", 60);
        $I->see("インストールが完了しました。");
        $I->click("/html/body/div[1]/div[3]/div[3]/div/div/div[3]/a");
    }
    public function プラグイン_有効化(AcceptanceTester $I){
        $I->amOnPage("/admin/store/plugin");
        //ステータスを確認
        $config = Fixtures::get('test_config');
        $span = '//*[@id="page_admin_store_plugin"]/div[1]/div[3]/div[2]/div/div/div[1]/div[2]/table/tbody/tr/td[3]/p[text() =  "'.$this->plugin_code.'"]';
        $tr = $span . '/ancestor::tr';
        $status = $tr . '/td[4]/span';
        $status_text = $I->grabTextFrom($status);
        echo("プラグインのステータス：". $status_text ."\n");
        $activateButton = $tr . '/td[6]/div/div[2]/a';
        $I->click($activateButton);
        $I->wait(3);
        $I->see("を有効にしました");
    }
    public function プラグイン_無効化(AcceptanceTester $I){
        $I->amOnPage("/admin/store/plugin");
        $span = '//*[@id="page_admin_store_plugin"]/div[1]/div[3]/div[2]/div/div/div[1]/div[2]/table/tbody/tr/td[3]/p[text() =  "'.$this->plugin_code.'"]';
        $tr = $span . '/ancestor::tr';
        $status = $tr . '/td[4]/span';
        $status_text = $I->grabTextFrom($status);
        echo("プラグインのステータス：". $status_text ."\n");
        $disableButton = $tr . '/td[6]/div/div[2]/a';
        $I->click($disableButton);
        $I->wait(3);
        $I->see("を無効にしました");
    }
    public function プラグイン_削除(AcceptanceTester $I){
        $I->amOnPage("/admin/store/plugin");
        $span = '//*[@id="page_admin_store_plugin"]/div[1]/div[3]/div[2]/div/div/div[1]/div[2]/table/tbody/tr/td[3]/p[text() =  "'.$this->plugin_code.'"]';
        $tr = $span . '/ancestor::tr';
        $uninstallButton = $tr . '/td[6]/div/div[1]/a';
        $I->click($uninstallButton);
        $I->wait(1);
        $I->click("/html/body/div[1]/div[3]/div[2]/div/div/div[1]/div[3]/div/div/div[3]/button[2]");
        $I->waitForJS("return $.active == 0;", 60);
        $I->see("削除が完了しました。");
        $I->click('//*[@id="officialPluginDeleteModal"]/div/div/div[3]/button[3]');
        $I->wait(3);
    }
}
