import { Game } from '../Game';
export class ChooseActionCard {
  game: Game;
  bga: ExtendedBga;

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  /**
   * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
   */
  onEnteringState(args: ChooseActionCardArgs, isCurrentPlayerActive: boolean) {
    if ((this.bga as any).players.isCurrentPlayerActive()) {
      args.strengths.forEach((strength) => {
        const label = `Take ${strength.type} (${strength.strength})`;
        this.bga.statusBar.addActionButton(label, () => {
          this.bga.actions.performAction('actChooseActionCard', { cardId: strength.id });
        });
      });
    }
  }

  /**
   * This method is called each time we are leaving the game state. You can use this method to perform some user interface changes at this moment.
   */
  onLeavingState(args: object, isCurrentPlayerActive: boolean) {}
}
