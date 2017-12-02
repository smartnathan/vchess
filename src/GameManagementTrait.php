<?php

namespace Drupal\vchess;

use Drupal\gamer\Entity\GamerStatistics;
use Drupal\user\UserInterface;
use Drupal\vchess\Entity\Game;
use Drupal\vchess\Game\GamePlay;

/**
 * Helper class to provide common game management functionality.
 */
trait GameManagementTrait {

  /**
   * Records the start of a new game and updates game and player info.
   *
   * @param \Drupal\vchess\Entity\Game $game
   * @param \Drupal\user\UserInterface $white_user
   * @param \Drupal\user\UserInterface $black_user
   *
   */
  public static function initializeGame(Game $game, UserInterface $white_user, UserInterface $black_user, UserInterface $challenger) {
    $white = GamerStatistics::loadForUser($white_user);
    $black = GamerStatistics::loadForUser($black_user);

    $white->setCurrent($white->getCurrent() + 1);
    $black->setCurrent($black->getCurrent() + 1);

    $white->save();
    $black->save();

    $game
      ->setWhiteUser($white_user)
      ->setBlackUser($black_user)
      ->setChallenger($challenger)
      ->setStatus(GamePlay::STATUS_IN_PROGRESS);

    $game->save();
  }

  public static function createChallenge(UserInterface $user, $time_per_move, $board_position) {
    $game = Game::create();
    $game
      ->setPlayerRandomly($user)
      ->setTimePerMove($time_per_move)
      ->setStatus(GamePlay::STATUS_AWAITING_PLAYERS)
      ->setBoard($board_position)
      ->setChallenger($user)
      ->save();
    return $game;
  }

}
