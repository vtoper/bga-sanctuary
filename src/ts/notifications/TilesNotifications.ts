import { players } from '../Players';

export class TilesNotifications {
  bga: ExtendedBga;

  constructor(bga: ExtendedBga) {
    this.bga = bga;
  }

  // Market refilled: replace all pool tiles
  async notif_fillPool(args) {
    players.setTilePool(args.tiles ?? []);
  }

  // Current player draws tiles from pool into hand
  async notif_drawTiles(args) {
    // Remove drawn tiles from pool
    for (const tileId of args.cardIds ?? []) {
      players.getPoolTileNode(tileId)?.remove();
    }
    // Update hand with new tiles
    players.setHand(args.tiles ?? []);
    players.setHandCount(args.player_id, args.handCount ?? (args.tiles ?? []).length);
  }

  // Opponent draws (tiles are hidden): remove from pool, update opponent's hand count
  async notif_pDrawCards(args) {
    for (const tileId of args.cardIds ?? []) {
      players.getPoolTileNode(tileId)?.remove();
    }
    if (args.handCount !== undefined) {
      players.setHandCount(args.player_id, args.handCount);
    }
  }

  // Animal tile played from hand onto the map
  async notif_animalPlayed(args) {
    players.removeHandTiles(args.player_id, [args.tile.id]);
    players.setTileOnBoard(args.player_id, args.tile);
    players.setHandCount(args.player_id, args.handCount ?? 0);
  }

  // Open area tile placed on the map
  async notif_openAreaPlaced(args) {
    players.removeHandTiles(args.player_id, [args.tile.id]);
    players.setTileOnBoard(args.player_id, args.tile);
    if (args.handCount !== undefined) {
      players.setHandCount(args.player_id, args.handCount);
    }
  }

  // Pouch tiles gained (tiles spent → pouch token added)
  async notif_pouchGained(args) {
    players.removeHandTiles(args.player_id, args.cardIds ?? []);
    players.setPouch(args.player_id, args.pouch);
  }

  // Action card moved/upgraded
  async notif_actionCardMoved(args) {
    players.setActionCards(args.player_id, args.actionCards);
  }

  // Building tile played from hand onto the map
  async notif_buildingPlayed(args) {
    players.removeHandTiles(args.player_id, [args.tile.id]);
    players.setTileOnBoard(args.player_id, args.tile);
    if (args.handCount !== undefined) {
      players.setHandCount(args.player_id, args.handCount);
    }
  }

  // Project tile played from hand onto the map
  async notif_projectPlayed(args) {
    players.removeHandTiles(args.player_id, [args.tile.id]);
    players.setTileOnBoard(args.player_id, args.tile);
    if (args.handCount !== undefined) {
      players.setHandCount(args.player_id, args.handCount);
    }
  }

  // Conservation marker placed (tiles spent from hand)
  async notif_conservationSupported(args) {
    players.removeHandTiles(args.player_id, args.cardIds ?? []);
    if (args.handCount !== undefined) {
      players.setHandCount(args.player_id, args.handCount);
    }
  }

  // Upgrade token used (action cards updated)
  async notif_upgradeTokenUsed(args) {
    if (args.actionCards) {
      players.setActionCards(args.player_id, args.actionCards);
    }
  }

  // Tile relocated on the board (moved from one cell to another)
  async notif_tileRelocated(args) {
    players.clearMapCell(args.player_id, args.fromX, args.fromY);
    players.setTileOnBoard(args.player_id, args.tile);
  }
}
