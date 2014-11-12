<?php

/**
 * @file
 * MCAvatar 0.6a
 * Written by Robin Kesseler
 */
class MCAvatar {

  // properties
  private $membersxml = '';
  private $pagenotice = '';
  private $tmppath = '';
  private $avatarimagepath = '';
  private $messages = array();
  //image properties
  private $left = 8;
  private $top = 8;
  private $crop_width = 8;
  private $crop_height = 8;
  private $new_width = 64;
  private $new_height = 64;

  // constructor
  /**
   * gather general info for the class
   *
   * @param $membersxmlpath
   *   (string) the path to the "members.xml" file, relative to the webroot.
   * @param $tmppath
   *   (string) The path to the temporary folder, relative to the webroot without traling slashes.
   * @param $avatarimagepath
   *   (string) The path to the folder, where the avatars are saved after processing, relative to the webroot without traling slashes.
   * @param $notice
   *   (string) Notice for showing underneath the avatars, this is optional.
   */
  public function __construct($membersxmlpath, $tmppath, $avatarimagepath, $notice = '') {
    $this->membersxml = $membersxmlpath;
    $this->tmppath = $tmppath;
    $this->avatarimagepath = $avatarimagepath;
    $this->pagenotice = $notice;
  }

  // public methods

  /**
   * Change output image settings
   * 
   * @param $cropoffsetleft
   *   (int) crop offset from the left. Defaults to 8 pixels.
   * @param $cropoffsettop
   *  (int) Crop offset from the top. Defaults to 8 pixels.
   * @param $cropwidth
   *  (int) Width to crop. Defaults to 8 pixels.
   * @param $cropheight
   *  (int) Height to crop. Defaults to 8 pixels.
   * @param $outputwidth
   *  (int) Width of the output image. Defaults to 64 pixels.
   * @param $outputheight
   *  (int) Height of the output image. Defaults to 64 pixels.
   */
  public function mca_set_image_settings($cropoffsetleft = 8, $cropoffsettop = 8, $cropwidth = 8, $cropheight = 8, $outputwidth = 64, $outputheight = 64) {
    $this->top = $cropoffsettop;
    $this->left = $cropoffsetleft;
    $this->crop_width = $cropwidth;
    $this->crop_height = $cropheight;
    $this->new_width = $outputwidth;
    $this->new_height = $outputheight;
  }

  /**
   * Update avatars with the minecraft servers.
   * This process takes a while, so it is recommended not to put this into the page builds
   */
  public function mca_update_avatars() {
    $users = $this->_mca_get_users();

    foreach ($users as $group => $usernames) {
      foreach ($usernames as $user) {
        $this->_mca_extract_userskin($user);
      }
    }
  }

  /**
   * Update the avatar for a single user and returns the new relative filepath.
   * 
   * @param $username
   *  (string) The username for wich to update the avatar.
   *  Take notice that this parameter is Case-Sensitive!
   */
  public function mca_update_user_avatar($username) {
    return $this->_mca_extract_userskin($username);
  }

  /**
   * Builds an ordered HTML for showing the avatars.
   *
   * Take notice that this function does not build an entire HTML-page, but only the part of the avatars!
   * This HTML is to be put inside the body-tags of your webpage.
   * Empty usergroups will be ommited.
   */
  public function mca_build_page() {
    $users = $this->_mca_get_users();
    $output = '';

    foreach ($users as $ugroup => $ugusers) {
      $output .= '<p class="member-list-title ' . $ugroup . 's">' . ucfirst($ugroup) . 's</p>';
      foreach ($ugusers as $user) {
        $output .= '<div class="member server-member"><img class="membertile" alt="' . $user . '" src="/' . $this->avatarimagepath . '/' . $user . '.jpg" /><br />' . ucfirst(strtolower($user)) . '</div>';
      }
    }

    if ($this->pagenotice != '') {
      $output .= '<div class="members-notice">' . $this->pagenotice . '</div>';
    }

    $this->_mca_add_message('Avatar page built successfully.');

    return $output;
  }

  /**
   * Returns a array with code-generated messages in the following layout:
   * [WEIGHT] => [NUMBER] => [MESSAGETEXT]
   * 
   * Possible weights are "notice", "warning" and "error".
   */
  public function mca_messages() {
    return $this->messages;
  }

  // private methods

  /**
   * returns an array with all users from the members.xml in respect to their membergroup.
   */
  private function _mca_get_users() {
    $members = array();
    $doc = new DOMDocument();
    $doc->load($this->membersxml);

    foreach ($doc->getElementsByTagName('ugroup') as $ugroup) {
      $members[$ugroup->nodeValue] = array();
      foreach ($doc->getElementsByTagName($ugroup->nodeValue) as $user) {
        array_push($members[$ugroup->nodeValue], $user->nodeValue);
      }
      usort($members[$ugroup->nodeValue], 'strnatcasecmp');
    }

    unset($doc);
    return $members;
  }

  /**
   * Gets the userskin from the minecraft servers and processes them into 64px x 64px images for their respective faces.
   *
   * @param $username
   *   (string) The username for wich to create the avatar.
   * 
   *   Take notice, this variable is case-sensitive!!!
   */
  private function _mca_extract_userskin($username) {
    $this->_mca_add_message('Attempting to create Usertile for "' . $username . '" ...');
    try {
      //get the users skin from minecraft.net and say where it needs to go.
      $skinurl = 'http://skins.minecraft.net/MinecraftSkins/' . $username . '.png';
      $tmpimagepath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/' . $this->tmppath . '/mca_tmp.png';
      $prmimagepath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/' . $this->avatarimagepath . '/' . $username . '.jpg';
      $file = false;

      //let's do this!
      if ($f = file_get_contents($skinurl)) {
        $file = file_put_contents($tmpimagepath, $f);
      }

      if ($file) {
        //crop it!
        $canvas = imagecreatetruecolor($this->crop_width, $this->crop_height);
        $current_image = imagecreatefrompng($tmpimagepath);
        imagecopy($canvas, $current_image, 0, 0, $this->left, $this->top, 64, 32);
        
        if($this->_mca_check_transparent($current_image)){
          imagecopy($canvas, $current_image, 0, 0, $this->left + 32, $this->top, 64, 32);
        }


        //resize it!
        $canvasResized = imagecreate($this->new_width, $this->new_height);
        imagecopyresized($canvasResized, $canvas, 0, 0, 0, 0, $this->new_width, $this->new_height, $this->crop_width, $this->crop_height);

        //Save that shit!
        //header('Content-type: image/jpeg');
        imagejpeg($canvasResized, $prmimagepath, 100);

        //set a message saying, I pulled this off like a boss!
        $this->_mca_add_message('Avatar created for "' . $username . '" in "' . $prmimagepath . '".', 'notice');

        //unset all objects
        unset($file);
        unset($canvas);
        unset($canvasResized);
        unset($current_image);

        //return the new filename...for ppl that want to play with it
        return '/' . $this->avatarimagepath . '/' . $username . '.jpg';
      } else {
        //my code couldn't possibly go error, so it must be a user typo or a non-existent user.
        $this->_mca_add_message('Unable to get skin for "' . $username . '"!', 'error');
        return '';
      }
    } catch (Exception $e) {
      //OK, something went awfully wrong here...HELP!!!
      $this->_mca_add_message('Unable to create avatar for "' . $username . '"! ' . $e->getMessage(), 'error');
      return '';
    }
  }

  /**
   * Adds code-generated messages to the "$messages" variable respective to their weight.
   *
   * @param $messagestring
   *   (string) message.
   * @param $weight
   *   (string) Severity of the message.
   *   Possible options are "notice", "warning" and "error".
   */
  private function _mca_add_message($messagestring, $weight = 'notice') {
    array_push($this->messages, array('weight' => $weight, 'message' => $messagestring));
  }

  /**
   * Check images for transparency
   * 
   *  @param $im
   *   (link resource) image link resource of the image to be checked
   */
  private function _mca_check_transparent($im) {

    $width = imagesx($im); // Get the width of the image
    $height = imagesy($im); // Get the height of the image
    // We run the image pixel by pixel and as soon as we find a transparent pixel we stop and return true.
    for ($i = 0; $i < $width; $i++) {
      for ($j = 0; $j < $height; $j++) {
        $rgba = imagecolorat($im, $i, $j);
        if (($rgba & 0x7F000000) >> 24) {
          return true;
        }
      }
    }

    // If we dont find any pixel the function will return false.
    return false;
  }

}
