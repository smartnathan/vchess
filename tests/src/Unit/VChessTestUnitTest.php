<?php

namespace Drupal\Tests\vchess\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Ensure that VChess functions properly.
 *
 * @group vchess
 */
class VChessTestUnitTest extends UnitTestCase {
  protected $privileged_user;
  
  /**
   * All sorts of tests
   * 
   * These tests are all bundled together into one function rather
   * than as individual functions to avoid the required setUp() function
   * having to be run each time.  This gives a massive (10x) increase
   * in speed
   */
  public function testAllSorts() {
    $game = new Game();

    //
    // Test initial board setup
    //
    $this->assertEqual($game->position(), BOARD_DEFAULT,
        "Check that the initial board setup is the standard initial position");
    
    //
    // Test promotion
    //
    $game = new Game();
    $game->setup_position("r3k3/1P6/8/8/8/8/8/4K3");
    
    $game->make_move(0, "Pb7xRa8=Q");
    
    $this->assertEqual($game->position(), "Q3k3/8/8/8/8/8/8/4K3");

    //
    //  Test checkmate
    //
    $game = new Game();
    $game->setup_position("3qk3/8/8/8/2B5/5Q2/8/4K3");
    
    // White queen delivers checkmate on f7 protected by a bishop on c4 
    $game->make_move(0, "Qf3-f7");
    
    $this->assertTrue($game->is_checkmate('b'), 
      "This should be checkmate from the queen protected by a bishop");
   
    //
    //Test invalid move cxc5
    //
    $game = new Game();
    $start_position = "4k3/8/8/2p5/8/8/2P5/4K3";
  
    $game->setup_position($start_position);
    
    // White pawn on c2 attempts to take black pawn on c5
    $game->make_move(0, "Pc2xPc5");
    
    $this->assertEqual($game->position(), $start_position, 
        "Test that the invalid pawn capture cxc5 is not allowed");
    
    //
    // Test check with piece that can capture checking piece
    //
    $game = new Game();
    $game->setup_position("2bk4/5Q2/8/4N3/8/8/8/4K3");
    $game->make_move(0, "Qf7-d7");
    
    $this->assertFalse($game->is_checkmate('b'), 
       "Bishop should be able to capture queen so it should not be checkmate");
 
    //
    // Test a move with check
    //
    $game = new Game();
    $game->setup_position("rnbqkbnr/ppppp1pp/8/5p2/4P3/8/PPPP1PPP/RNBQKBNR");
  
    $game->make_move(0, "Qd1-h5");
  
    $this->assertTrue($game->is_check('b'), "King should be in check");
    $this->assertEqual($game->turn(), 'b', "It should now be black's turn");
  
    //
    //     Test short castling
    //
        $game = new Game();
    $game->setup_position("4k3/8/8/8/8/8/5PPP/4K2R");
    
    $game->make_move(0, "Ke1-g1");
    $this->assertEqual($game->position(), "4k3/8/8/8/8/8/5PPP/5RK1",
        "Check position after white king has castled kingside: " . $game->position());

    //
    // Test en passant
    //
    $game = new Game();
    $game->setup_position(BOARD_DEFAULT);
    $game->make_move(0, "Pe2-e4");  // 1. e4
    $game->make_move(0, "Nb8-c6");  // 1. ... Nc6
    $game->make_move(0, "Pe4-e5");  // 2. e5
    $game->make_move(0, "Pd7-d5");  // 2. ... d5
    $game->make_move(0, "Pe5-d6");  // 3. exd6
    
    $this->assertEqual($game->position(), "r1bqkbnr/ppp1pppp/2nP4/8/8/8/PPPP1PPP/RNBQKBNR",
        "Check board after en passant capture: " . $game->position());
    $this->assertEqual($game->last_move()->algebraic(), "exd6", 
        "Check en passant algebraic move: " . $game->last_move()->algebraic());
    
//     if ($game->position() == "r1bqkbnr/ppp1pppp/2nP4/8/8/8/PPPP1PPP/RNBQKBNR") {
//       drupal_set_message("All OK with the position!");
//     }
//     else {
//       drupal_set_message("Houston, we have a problem");
//     }
//     if ($game->last_move()->algebraic() == "exd6") {
//       drupal_set_message("All OK with the algebraic move!");
//     }
//     else {
//       drupal_set_message("Houston, we have another problem");
//     }

    //
    // Test stalemate handling
    //
    $game = new Game();   
    $game->setup_position("4k3/R7/3Q5/8/8/8/8/4K3");
    $game->set_players(0, 0); // should change status to "in progress"
    
    $this->assertEqual($game->status(), STATUS_IN_PROGRESS, 
        "Check game status is 'in progress' before stalemate move");

    $game->make_move(0, "Ra7-b7");

    $this->assertEqual($game->status(), STATUS_DRAW, 
        "Check game status is 'draw' after stalemate move");
  }
}