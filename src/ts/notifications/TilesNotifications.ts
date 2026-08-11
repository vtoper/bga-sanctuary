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
}
