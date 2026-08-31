import { Game } from '../Game';
import { players } from '../Players';
import { onClick } from '../framework/event';
import { clearPossible, performAction } from '../framework/utils';

export class Hunter {
  game: Game;
  bga: ExtendedBga;

  private args: HunterArgs | null = null;
  private selectedCardId: string | null = null;

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  onEnteringState(args: HunterArgs, isCurrentPlayerActive: boolean) {
    this.args = args;
    this.selectedCardId = null;
    if (isCurrentPlayerActive) {
      this.refresh();
    }
  }

  onLeavingState(args: object, isCurrentPlayerActive: boolean) {
    this.args = null;
    this.selectedCardId = null;
    clearPossible();
  }

  private refresh() {
    clearPossible();
    this.makeAnimalTilesSelectable();
    this.updateStatusBar();
  }

  private makeAnimalTilesSelectable() {
    for (const cardId of this.args?.cardIds ?? []) {
      const node = players.getHandTileNode(cardId);
      if (!node) {
        continue;
      }

      node.classList.toggle('selected', this.selectedCardId === cardId);
      onClick(node, () => this.selectCard(cardId));
    }
  }

  private selectCard(cardId: string) {
    this.selectedCardId = this.selectedCardId === cardId ? null : cardId;
    this.refresh();
  }

  private updateStatusBar() {
    this.bga.statusBar.removeActionButtons();
    this.bga.statusBar.addActionButton(
      _('Keep Animal'),
      () => performAction('actHunter', { cardIds: JSON.stringify([this.selectedCardId]) }),
      { disabled: this.selectedCardId === null },
    );
  }
}
