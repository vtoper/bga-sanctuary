export class TilesNotifications {
  bga: ExtendedBga;

  constructor(bga: ExtendedBga) {
    this.bga = bga;
  }

  async notif_fillPool(args) {
    console.debug(args);
  }

  async notif_drawTiles(args) {
    console.debug(args);
  }

  async notif_pDrawCards(args) {
    console.debug(args);
  }

  async notif_animalPlayed(args) {
    console.debug(args);
  }

  async notif_buildingPlayed(args) {
    console.debug(args);
  }

  async notif_projectPlayed(args) {
    console.debug(args);
  }

  async notif_conservationSupported(args) {
    console.debug(args);
  }
}
