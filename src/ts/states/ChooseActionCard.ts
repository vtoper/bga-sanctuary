import { Game } from '../Game';
import { players } from '../Players';
import { onClick } from '../framework/event';
import { clearPossible, getCurrentPlayerId, performAction } from '../framework/utils';

export class ChooseActionCard {
  game: Game;
  bga: ExtendedBga;

  private args: ChooseActionCardArgs | null = null;

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  /**
   * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
   */
  onEnteringState(args: ChooseActionCardArgs, isCurrentPlayerActive: boolean) {
    this.args = args;
    if (!isCurrentPlayerActive) {
      return;
    }

    this.refresh();
  }

  /**
   * This method is called each time we are leaving the game state. You can use this method to perform some user interface changes at this moment.
   */
  onLeavingState(args: object, isCurrentPlayerActive: boolean) {
    this.args = null;
    clearPossible();
  }

  /**
   * Each choice can be taken either by clicking its card on the board or by its status bar button.
   */
  private refresh() {
    clearPossible();
    this.bga.statusBar.removeActionButtons();

    const playerId = getCurrentPlayerId();
    for (const choice of this.args?.strengths ?? []) {
      const cardNode = players.getActionCardNode(playerId, choice.id);
      if (cardNode) {
        onClick(cardNode, () => this.chooseCard(choice.id));
      }

      this.bga.statusBar.addActionButton(`${_('Take')} ${choice.type} (${choice.strength})`, () =>
        this.chooseCard(choice.id),
      );
    }
  }

  private chooseCard(cardId: number) {
    performAction('actChooseActionCard', { cardId });
  }
}
