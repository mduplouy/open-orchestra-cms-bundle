Site = Backbone.Model.extend({})
Block = Backbone.Model.extend({})
BlockCollection = Backbone.Collection.extend(model: Block)
Area = Backbone.Model.extend(
  blocks: BlockCollection
  areas: AreaCollection
)
AreaCollection = Backbone.Collection.extend(model: Block)
Node = Backbone.Model.extend(
  areas: AreaCollection,
  blocks: BlockCollection
)
NodeCollection = Backbone.Collection.extend(model: Node)
NodeCollectionElement = Backbone.Model.extend(
  nodes: NodeCollection
)
Template = Backbone.Model.extend(
  areas: AreaCollection
)
TableviewModel = Backbone.Model.extend({})
TableviewCollection = Backbone.Collection.extend(model: TableviewModel)
TableviewElement = Backbone.Model.extend({})
VersionviewModel = Backbone.Model.extend({})
VersionviewElement = Backbone.Model.extend({})
MediaModel = Backbone.Model.extend({})
MediaCollection = Backbone.Collection.extend(model: MediaModel)
MediaElement = Backbone.Model.extend(
  sites: MediaCollection
)
