<?php

class TrashfilesField extends BaseField {
  static public $fieldname = 'trashfiles';
  static public $assets = array(
    'js' => array(
      'script.js',
    )
  );

  public function input() {
    // Load template with arguments
    $site = kirby()->site();
    if(!$site->multilang() || ($site->multilang() && $site->language() == $site->defaultLanguage())) {
      $html = tpl::load( __DIR__ . DS . 'template.php', $data = array(
        'field' => $this,
        'site' => $site,
        'page' => $this->page(),
        'text' => $this->i18n($this->text()),
      ));
      return $html;
    } else {
      return false;
    }
  }

  public function result() {
    return null;
  }

  public function element() {
    $element = parent::element();
    $element->data('field', self::$fieldname);
    return $element;
  }

  public function getFiles() {
    return $this->page()->files();
  }

  public function getData($lang = null) {
    $site = kirby()->site();
    if($site->multilang()) {
      $lDefaultCode = $site->defaultLanguage()->code();
      return $this->page()->content($lDefaultCode)->toArray();
    } if(is_string($lang)) {
      return $this->page()->content($lang)->toArray();
    } else {
      return $this->page()->content()->toArray();
    }

  }

  public function updatePage($newPage, $newID) {
    $site = kirby()->site();
    foreach($site->languages() as $l) {
      if($l !== $site->defaultLanguage()) {
        $data = $this->getData($l->code());
        $data['title'] = urldecode($newID);
        try {
          $newPage->update($data, $l->code());
          return true;
        } catch(Exception $e) {
          return false;
        }
      }
    }
  }

  public function copyFiles($files, $newPage) {
    foreach($files as $file) {
    try {
      $file->copy(kirby()->roots()->content() . '/' . $newPage->diruri() . "/" . $file->filename());
    } catch (Exception $e) {
      return false;
    }
    }
    return true;
  }

  public function copyMetaFiles($newPage) {
    $metaFiles = $this->page()->inventory()['meta'];
    $source = kirby()->roots()->content() . DS . $this->page()->diruri();
    $target = kirby()->roots()->content() . DS . $newPage->diruri();

    // different ways to get metafiles in single and multi-lingual environments
    foreach($metaFiles as $file) {
      if(is_array($file)) {
        foreach($file as $key => $filename) {
          f::copy($source . "/" . $filename, $target . "/" . $filename);
        }

      } else {
        f::copy($source . "/" . $file, $target . "/" . $file);
      }
    }
  }

  // Routes
  public function routes() {
    return array(
      array(
        'pattern' => 'ajax/(:any)',
        'method'  => 'GET',
        'action' => function($newID) {

          $site = kirby()->site();
          $page = $this->page();

          // fetch all files
          $files = $this->getfiles();

          // try to create the new page
          try {
            foreach ($files as $file) $file->delete();

            $response = array('message' => 'The page was successfully created.', 'class' => 'success');
            return json_encode($response);

          } catch(Exception $e) {
            $response = array('message' => $e->getMessage(), 'class' => 'error');
            return json_encode($response);
          }
        }
      )
    );

  }
}
