<?php

namespace Drupal\vchess\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\vchess\Entity\Game;
use Drupal\vchess\Game\Board;

/**
 * @Block(
 *   id = "vchess_captured_pieces",
 *   admin_label = @Translation("VChess captured pieces"),
 *   category = @Translation("VChess")
 * )
 */
class CapturedPiecesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (($game_id = \Drupal::request()->get('vchess_game')) && ($game = Game::load($game_id))) {
      return static::buildContent($game);
    }
    return ['#markup' => ''];
  }

  public static function buildContent(Game $game) {
    $class = 'vchess-captured-pieces';
    $id = Html::getUniqueId($class);
    $board = (new Board())->setupPosition($game->getBoard());
    return [
      '#cache' => [
        'max-age' => 0,
      ],
      '#theme' => 'vchess_captured_pieces',
      '#pieces' => $board->getCapturedPieces(),
      '#prefix' => '<div id="' . $id . '" class="' . $class . '">',
      '#suffix' => '</div>',
    ];
  }

}
