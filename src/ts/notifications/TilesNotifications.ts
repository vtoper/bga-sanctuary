import { players } from '../Players';

/**
 * Handlers for every tile-related notification sent by the PHP side.
 * The name after `notif_` must match the string passed to `$this->notify->all(...)`.
 */
export class TilesNotifications {
  bga: ExtendedBga;

  constructor(bga: ExtendedBga) {
    this.bga = bga;
  }

  // Tiles::fillPool — `pool` holds the whole market after sliding left and drawing
  async notif_fillPool(args) {
    players.setTilePool(args.pool ?? []);
  }

  // TakeTile::actTakeTile — tiles slide from their market slot into the player's hand
  async notif_takeTiles(args) {
    await players.takeTilesFromPool(args.player_id, args.cards ?? []);
  }

  // Tiles::draw, public part — only the number of tiles is public
  async notif_drawTiles(args) {
    players.incHandCount(args.player_id, args.n ?? 0);
  }

  // Tiles::draw, private part — the drawing player also gets the actual tiles
  async notif_pDrawCards(args) {
    players.addTilesToHand(args.player_id, args.cards ?? []);
  }

  // Tiles::notificationDiscardCards, public part — only the number of tiles is public
  async notif_discardCards(args) {
    players.incHandCount(args.player_id, -(args.n ?? 0));
  }

  // Tiles::notificationDiscardCards, private part — the discarding player gets the actual tiles
  async notif_pDiscardCards(args) {
    const tileIds = (args.cards ?? []).map((tile) => tile.id);
    players.removeHandTiles(args.player_id, tileIds);
    players.incHandCount(args.player_id, -tileIds.length);
  }

  // Administration::actDiscard — tiles discarded down to the hand limit
  async notif_discardTiles(args) {
    const tileIds = (args.cards ?? []).map((tile) => tile.id);
    players.removeHandTiles(args.player_id, tileIds);
    players.incHandCount(args.player_id, -tileIds.length);
  }

  // Administration::finishAdministration — discard pile shuffled back into the deck
  // TODO: nothing to update yet, the deck is not displayed. `args.deckCount` holds the new size.
  async notif_deckReformed(args) {}

  // Animal::actPlayAnimal — animal tile played from hand onto the map
  async notif_animalPlayed(args) {
    players.playTileFromHand(args.player_id, args.animal);
  }

  // Building::actPlayBuilding and Project::actPlayProject (non-release projects)
  async notif_buildingPlayed(args) {
    players.playTileFromHand(args.player_id, args.building ?? args.project);
  }

  // Project::actPlayProject — release project replaces a tile already on the map
  async notif_projectReleased(args) {
    if (args.existingId) {
      players.removeTileFromBoard(args.player_id, args.existingId);
    }
    players.playTileFromHand(args.player_id, args.project);
  }

  // PlaceOpenAreas::actPlaceOpenArea — open area tile placed on the map
  async notif_openAreaPlaced(args) {
    players.playTileFromHand(args.player_id, args.openArea);
  }

  // Relocate::actRelocate — tile moved from one cell of the map to another
  async notif_tileRelocated(args) {
    players.clearMapCell(args.player_id, args.fromX, args.fromY);
    players.setTileOnBoard(args.player_id, args.tile);
  }

  // Pouch::actPouch — tiles spent from hand in exchange for pouch markers
  async notif_pouchGained(args) {
    const tileIds = args.cardIds ?? [];
    players.removeHandTiles(args.player_id, tileIds);
    players.incHandCount(args.player_id, -tileIds.length);
    players.setPouch(args.player_id, args.pouch);
  }
}
