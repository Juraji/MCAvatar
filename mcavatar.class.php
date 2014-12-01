<?php

/**
 * @file
 * MCAvatar 0.6a
 * Written by Robin Kesseler
 */
class MCAvatar {

  // properties
  private $membersXML = '';
  private $pageNotice = '';
  private $tmpPath = '';
  private $avatarImagePath = '';
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
   * @param $membersXMLPath
   *   (string) the path to the "members.xml" file, relative to the webroot.
   * @param $tmpPath
   *   (string) The path to the temporary folder, relative to the webroot without traling slashes.
   * @param $avatarImagePath
   *   (string) The path to the folder, where the avatars are saved after processing, relative to the webroot without traling slashes.
   * @param $notice
   *   (string) Notice for showing underneath the avatars, this is optional.
   */
  public function __construct($membersXMLPath, $tmpPath, $avatarImagePath, $notice = '') {
    $this->membersXML = $membersXMLPath;
    $this->tmpPath = $tmpPath;
    $this->avatarImagePath = $avatarImagePath;
    $this->pageNotice = $notice;
  }

  // public methods

  /**
   * Change output image settings
   * 
   * @param $cropOffsetLeft
   *   (int) crop offset from the left. Defaults to 8 pixels.
   * @param $cropOffsetTop
   *  (int) Crop offset from the top. Defaults to 8 pixels.
   * @param $cropWidth
   *  (int) Width to crop. Defaults to 8 pixels.
   * @param $cropHeight
   *  (int) Height to crop. Defaults to 8 pixels.
   * @param $outputWidth
   *  (int) Width of the output image. Defaults to 64 pixels.
   * @param $outputHeight
   *  (int) Height of the output image. Defaults to 64 pixels.
   */
  public function mca_set_image_settings($cropOffsetLeft = 8, $cropOffsetTop = 8, $cropWidth = 8, $cropHeight = 8, $outputWidth = 64, $outputHeight = 64) {
    $this->top = $cropOffsetTop;
    $this->left = $cropOffsetLeft;
    $this->crop_width = $cropWidth;
    $this->crop_height = $cropHeight;
    $this->new_width = $outputWidth;
    $this->new_height = $outputHeight;
  }

  /**
   * Update avatars with the minecraft servers.
   * This process takes a while, so it is recommended not to put this into the page builds
   */
  public function mca_update_avatars() {
    $users = $this->_mca_get_users();

    foreach ($users as $group => $userNames) {
      foreach ($userNames as $user) {
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
   * @return string
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

    foreach ($users as $uGroup => $ugUsers) {
      $output .= '<p class="member-list-title ' . $uGroup . 's">' . ucfirst($uGroup) . 's</p>';
      foreach ($ugUsers as $user) {
        $output .= '<div class="member server-member"><img class="member-tile" alt="' . $user . '" src="/' . $this->avatarImagePath . '/' . $user . '.jpg" /><br />' . ucfirst(strtolower($user)) . '</div>';
      }
    }

    if ($this->pageNotice != '') {
      $output .= '<div class="members-notice">' . $this->pageNotice . '</div>';
    }

    $this->_mca_add_message('Avatar page built successfully.');

    return $output;
  }

  /**
   * Returns a array with code-generated messages in the following layout:
   * [WEIGHT] => [NUMBER] => [MESSAGE-TEXT]
   * 
   * Possible weights are "notice", "warning" and "error".
   */
  public function mca_messages() {
    return $this->messages;
  }

  // private methods

  /**
   * returns an array with all users from the members.xml in respect to their member-group.
   */
  private function _mca_get_users() {
    $members = array();
    $doc = new DOMDocument();
    $doc->load($this->membersXML);

    foreach ($doc->getElementsByTagName('uGroup') as $uGroup) {
      $members[$uGroup->nodeValue] = array();
      foreach ($doc->getElementsByTagName($uGroup->nodeValue) as $user) {
        array_push($members[$uGroup->nodeValue], $user->nodeValue);
      }
      usort($members[$uGroup->nodeValue], 'strnatcasecmp');
    }

    unset($doc);
    return $members;
  }

  /**
   * Gets the userskin from the minecraft servers and processes them into 64px x 64px images for their respective faces.
   *   Take notice, this variable is case-sensitive!!!
   *
   * @param $username
   *   (string) The username for wich to create the avatar.
   *
   * @return string
   */
  private function _mca_extract_userskin($username) {
    $this->_mca_add_message('Attempting to create User tile for "' . $username . '" ...');
    try {
      //get the users skin from minecraft.net and say where it needs to go.
      $skinURL = 'http://skins.minecraft.net/MinecraftSkins/' . $username . '.png';
      $tmpImagePath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/' . $this->tmpPath . '/mca_tmp.png';
      $prmImagePath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/' . $this->avatarImagePath . '/' . $username . '.jpg';
      $file = false;

      //let's do this!
      if ($f = file_get_contents($skinURL)) {
        $file = file_put_contents($tmpImagePath, $f);
      }

      if ($file) {
        //crop it!
        $canvas = imagecreatetruecolor($this->crop_width, $this->crop_height);
        $current_image = imagecreatefrompng($tmpImagePath);
        imagecopy($canvas, $current_image, 0, 0, $this->left, $this->top, 64, 32);
        
        if($this->_mca_check_transparent($current_image)){
          imagecopy($canvas, $current_image, 0, 0, $this->left + 32, $this->top, 64, 32);
        }


        //resize it!
        $canvasResize = imagecreate($this->new_width, $this->new_height);
        imagecopyresized($canvasResize, $canvas, 0, 0, 0, 0, $this->new_width, $this->new_height, $this->crop_width, $this->crop_height);

        //Save that shit!
        //header('Content-type: image/jpeg');
        imagejpeg($canvasResize, $prmImagePath, 100);

        //set a message saying, I pulled this off like a boss!
        $this->_mca_add_message('Avatar created for "' . $username . '" in "' . $prmImagePath . '".', 'notice');

        //unset all objects
        unset($file);
        unset($canvas);
        unset($canvasResize);
        unset($current_image);

        //return the new filename...for ppl that want to play with it
        return '/' . $this->avatarImagePath . '/' . $username . '.jpg';
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
   * @param $messageString
   *   (string) message.
   * @param $weight
   *   (string) Severity of the message.
   *   Possible options are "notice", "warning" and "error".
   */
  private function _mca_add_message($messageString, $weight = 'notice') {
    array_push($this->messages, array('weight' => $weight, 'message' => $messageString));
  }

  /**
   * Check images for transparency
   *
   * @param $im
   *   (link resource) image link resource of the image to be checked
   * @return bool
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
