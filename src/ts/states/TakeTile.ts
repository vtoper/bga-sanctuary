import { Game } from '../Game';

export class TakeTile {
  game: Game;
  bga: ExtendedBga;

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  /**
   * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
   */
  onEnteringState(args: object, isCurrentPlayerActive: boolean) {}

  /**
   * This method is called each time we are leaving the game state. You can use this method to perform some user interface changes at this moment.
   */
  onLeavingState(args: object, isCurrentPlayerActive: boolean) {}
}
