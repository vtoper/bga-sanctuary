import { Game } from '../Game';
import { players } from '../Players';
import { onClick } from '../framework/event';
import { clearPossible, getCurrentPlayerId, performAction } from '../framework/utils';

export class PlayAnimal {
  game: Game;
  bga: ExtendedBga;

  private args: PlayAnimalArgs | null = null;
  private selectedTileId: string | null = null;
  private selectedLocation: string | null = null;
  private selectedOpenAreas: string[] = [];

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  /**
   * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
   */
  onEnteringState(args: PlayAnimalArgs, isCurrentPlayerActive: boolean) {
    this.args = args;
    this.resetSelection();

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
    this.resetSelection();
    clearPossible();
  }

  private resetSelection() {
    this.selectedTileId = null;
    this.selectedLocation = null;
    this.selectedOpenAreas = [];
  }

  private refresh() {
    clearPossible();
    this.makeHandSelectable();
    this.makeLocationsSelectable();
    this.updateStatusBar();
  }

  /**
   * Cells that must be covered by an open area for the current animal/location selection.
   */
  private getRequiredOpenAreas(): SanctuaryCell[] {
    if (!this.args || !this.selectedTileId || !this.selectedLocation) {
      return [];
    }

    const needed = this.args.neededOpenAreas?.[this.selectedTileId];
    if (!needed || Array.isArray(needed)) {
      return []; // an empty PHP array is serialized as []
    }

    return needed[this.selectedLocation] ?? [];
  }

  private makeHandSelectable() {
    const requiredOpenAreas = this.getRequiredOpenAreas().length;

    for (const tileId of players.getHandTileIds()) {
      const node = players.getHandTileNode(tileId);
      if (!node) {
        continue;
      }

      if (tileId === this.selectedTileId) {
        node.classList.add('selected');
        onClick(node, () => this.selectAnimal(null));
      } else if (requiredOpenAreas > 0) {
        // any other tile of the hand can be discarded as an open area
        if (this.selectedOpenAreas.includes(tileId)) {
          node.classList.add('selected');
        }
        onClick(node, () => this.toggleOpenArea(tileId));
      } else if (this.args.playableTiles[tileId]) {
        onClick(node, () => this.selectAnimal(tileId));
      }
    }
  }

  private makeLocationsSelectable() {
    if (!this.selectedTileId) {
      return;
    }

    const playerId = getCurrentPlayerId();
    for (const cell of this.args.playableTiles[this.selectedTileId] ?? []) {
      const node = players.getMapCellNode(playerId, cell.x, cell.y);
      if (!node) {
        continue;
      }

      const locationKey = `${cell.x}_${cell.y}`;
      if (locationKey === this.selectedLocation) {
        node.classList.add('selected');
      }
      onClick(node, () => this.selectLocation(locationKey));
    }
  }

  private selectAnimal(tileId: string | null) {
    this.resetSelection();
    this.selectedTileId = tileId;
    this.refresh();
  }

  private selectLocation(locationKey: string) {
    this.selectedLocation = this.selectedLocation === locationKey ? null : locationKey;
    this.selectedOpenAreas = [];
    this.refresh();
  }

  private toggleOpenArea(tileId: string) {
    const required = this.getRequiredOpenAreas().length;
    const index = this.selectedOpenAreas.indexOf(tileId);

    if (index >= 0) {
      this.selectedOpenAreas.splice(index, 1);
    } else {
      if (this.selectedOpenAreas.length >= required) {
        this.selectedOpenAreas.shift();
      }
      this.selectedOpenAreas.push(tileId);
    }

    this.refresh();
  }

  private updateStatusBar() {
    this.bga.statusBar.removeActionButtons();

    const required = this.getRequiredOpenAreas().length;
    let label = _('Select an animal');
    if (this.selectedTileId && !this.selectedLocation) {
      label = _('Select a location');
    } else if (this.selectedTileId && this.selectedOpenAreas.length < required) {
      label = `${_('Select the open areas')} (${this.selectedOpenAreas.length}/${required})`;
    } else if (this.selectedTileId) {
      label = _('Confirm');
    }

    const ready = !!this.selectedTileId && !!this.selectedLocation && this.selectedOpenAreas.length === required;
    this.bga.statusBar.addActionButton(label, () => this.confirm(), { disabled: !ready });
  }

  private confirm() {
    performAction('actPlayAnimal', {
      tileId: this.selectedTileId,
      location: this.selectedLocation,
      openAreas: JSON.stringify(this.selectedOpenAreas),
    });
  }
}
