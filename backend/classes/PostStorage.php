<?php
//require_once '../config.php';
class PostStorage extends DBConnection
{

	//Attributs de la classe
	//--------------------------------------------------------------------------------
	private $id;
	private $postDate;
	private $content;
	private $imgPath;
	private $likes;
	private $reposts;
	private $views;
	private $idPostCommented;
	private $idPostReposted;
	private $idAuthor;
	private $settings;

	private $BANNED_TEMPORALY = "BANNED_TMP";

	//Constructeur : prend une ligne de la réponse SQL comme paramètre
	//(c'est à dire le résultat de mysqli->fetch_assoc() )
	//--------------------------------------------------------------------------------
	function __construct(&$row)
	{
		$this->id = $row["id_post"];
		$this->postDate = $row["post_date"];
		$this->content = $row["content_post"];
		$this->imgPath = $row["media_post"];
		$this->likes = $row["likes"];
		$this->reposts = $row["reposts"];
		$this->views = $row["views"];
		$this->idPostCommented = $row["id_post_comment"];
		$this->idPostReposted = $row["id_post_repost"];
		$this->idAuthor = $row["id_user"];
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}

	public function __destruct()
	{
		parent::__destruct();
	}

	//Méthode pour "echo" le HTML correspondant à ce post.
	//Une information vient de l'extérieur : 
	//--------------------------------------------------------------------------------
	function display_to_html()
	{

		//conversion temps "database" vers timestamp UNIX
		$timestamp = strtotime($this->postDate);

		//Récupération des informations du propriétaire du post
		$ownerQuery = "SELECT * FROM `user` WHERE id_user = " . $this->idAuthor;
		$ownerResult = $this->conn->query($ownerQuery);
		if ($ownerResult->num_rows > 0) {
			$owner = $ownerResult->fetch_assoc();
		}
		$isOwnerBannedTmp = ($owner['banned_temporarly'] != 0) ? true : false;
		if ($isOwnerBannedTmp) {
			$process = $this->conn->query("SELECT * FROM interdiction WHERE id_user = '{$owner['id_user']}' AND actif = 0 AND code_interdiction_type = '{$this->BANNED_TEMPORALY}'");
			if ($process->num_rows > 0) {
				$data = $process->fetch_array();
				$banRemainedTime = $this->remainTime($data['interdiction_date'], $data['delay']);
			} else {
				$banRemainedTime = 0;
			}
		}

		//Compter le nombre de commentaire 
		//SELECT COUNT(id_post) FROM post where id_post_comment = 25;
		$commentQuery = "SELECT COUNT(id_post) as comments FROM post where removed = 0 and id_post_comment = " . $this->id;
		$commentResult = $this->conn->query($commentQuery);
		if ($commentResult->num_rows != 0) {
			$comments = $commentResult->fetch_assoc();
		}

		//Vérifier si l'utilisateur connecté à liké le post
		$idUser = $this->settings->userdata('id_user');
		$isLike = false;
		$likeQuery = $this->conn->query("SELECT id_post_like FROM `post_like` where id_post = $this->id and id_user = $idUser")->num_rows > 0;

		//Vérifier si l'utilisateur connecté suit ou pas l'autheur du post qui n'est pas lui
		$ownerFollowers = $this->conn->query("SELECT * FROM followship f JOIN post p ON f.id_user_following = p.id_user WHERE p.id_user = $this->idAuthor AND p.id_user != '{$this->settings->userdata('id_user')}' AND f.id_user_follower = '{$this->settings->userdata('id_user')}' ")->num_rows;

		//Vérifier si un post a été marqué comme sensible
		$sensitive = "SENSIBLE";
		$isSensitive = $this->conn->query("SELECT * FROM notification n INNER JOIN interdiction i ON n.id_interdiction = i.id_interdiction WHERE n.id_post = {$this->id} AND i.code_interdiction_type = '{$sensitive}' AND i.actif = 0 ")->num_rows > 0;

		//Sortir ("echo") le HTML suivant :
		?>

		<div class="central-meta item">
			<div class="user-post">
				<div class="friend-info">
					<figure>
						<img src="<?php echo validate_image($owner['avatar']) ?>" alt="" height="60" width="60" class="">
					</figure>
					<div class="friend-name post" data-id="<?= $this->id ?>">
						<ins><a href="<?php echo BASE_URL . 'user/user/?id=' . $owner['id_user'] ?>"
								title=""><?php echo $owner['firstname'] . ' ' . $owner['lastname']; ?></a><span
								class="text-danger"><?php echo $isOwnerBannedTmp ? ' (Banni temporairement : à peu près ' . $banRemainedTime . ' heures restants)' : '' ?></span></ins>
						<span><?php echo $this->displayUsername($owner['username']) ?></span>
						<span>publié le
							<?php echo date("d/m/y à h:i:s", $timestamp) . " (" . $this->relativeDate($this->postDate) . ")"; ?></span>
					</div>

					<?php if ($isSensitive): ?>
						<div class="description sensitive text-center">
							<a id="showPost" href="javascript:void(0)" class="btn btn-danger">Post sensible et potentiellement
								choquant. Voir quand même en cliquant ici.</a>
						</div>
					<?php endif; ?>

					<div id="originalContent" class="render" <?php echo $isSensitive ? 'style="display: none;"' : '' ?>>
						<div class="description post" data-id="<?= $this->id ?>">
							<p>
								<?php echo $this->content; ?>
							</p>
						</div>
						<div class="post-meta">
							<?php if (!empty($this->imgPath)) { ?>
								<?php if (!filter_var($this->imgPath, FILTER_VALIDATE_URL)): ?>
									<img src="<?php echo validate_image($this->imgPath) ?>" class="post" alt=""
										data-id="<?= $this->id ?>">
								<?php else: ?>
									<iframe width="" height="315" src="<?php echo $this->imgPath ?>" allow="autoplay;"
										allowfullscreen></iframe>
								<?php endif; ?>
							<?php } ?>
						</div>
					</div>

					<div class="we-video-info">
						<ul>
							<li>
								<span class="views" data-toggle="tooltip" title="Vues">
									<i class="fa fa-eye"></i>
									<ins><?php echo $this->format_large_number($this->views); ?></ins>
								</span>
							</li>
							<li>
								<span class="comment post_comments" data-id="<?= $this->id ?>" data-toggle="tooltip"
									title="Commentaire">
									<i class="fa fa-comments-o"></i>
									<ins
										class="comment-count"><?php echo $this->format_large_number($comments['comments']); ?></ins>
								</span>
							</li>
							<li>
								<?php if (isset($likeQuery) && !!$likeQuery) { ?>
									<a>
										<span class="like_post" data-toggle="tooltip" title="J'aime/J'aime pas" data-like='true'
											data-id="<?= $this->id ?>">
											<i class="fa fa-heart text-danger"></i>
											<ins class="like-count"><?= $this->format_large_number($this->likes); ?></ins>
										</span>
									</a>
								<?php } else { ?>
									<a>
										<span class="like_post" data-toggle="tooltip" title="J'aime/J'aime pas" data-like='false'
											class="like_post" data-id="<?= $this->id ?>">
											<i class="far fa-heart"></i>
											<ins class="like-count"><?= $this->format_large_number($this->likes); ?></ins>
										</span>
									</a>
								<?php } ?>

							</li>

							<li>
								<?php if ($this->idAuthor != $this->settings->userdata('id_user')): ?>
									<?php if ($ownerFollowers == 0): ?>

										<a href="javascript:void(0)" class="follow" title="" data-ripple=""
											data-id='<?= $this->idAuthor ?>' data-follow='false'>
											<span data-toggle="tooltip" title="Suivre">
												<i class="fa fa-plus-circle text-primary"></i>
												<ins></ins>
											</span>
										</a>

									<?php else: ?>
										<a href="javascript:void(0)" class="follow" title="" data-ripple=""
											data-id='<?= $this->idAuthor ?>' data-follow='true'>
											<span data-toggle="tooltip" title="Ne plus suivre">
												<i class="fa fa-minus-circle text-danger"></i>
												<ins></ins>
											</span>
										</a>

									<?php endif; ?>
								<?php endif; ?>
							</li>

							<!-- <li class="social-media">
									<div class="menu">
										<div class="btn trigger"><i class="fa fa-share-alt"></i></div>

										<div class="rotater">
											<div class="btn btn-icon"><a href="#" title=""><i class="fa fa-facebook"></i></a>
											</div>
										</div>
										<div class="rotater">
											<div class="btn btn-icon"><a href="#" title=""><i class="fa fa-twitter"></i></a>
											</div>
										</div>
										<div class="rotater">
											<div class="btn btn-icon"><a href="#" title=""><i class="fa fa-instagram"></i></a>
											</div>
										</div>
									</div>
								</li> -->
						</ul>
					</div>

				</div>

				<!-- Affichage des commentaires -->
				<div class="coment-area">
					<ul class="we-comet">
						<?php
						$comment_qry = $this->conn->query("SELECT p.*, concat(u.firstname, ' ',u.lastname) as `name` , u.avatar FROM `post` p inner join `user` u on p.id_user = u.id_user WHERE p.removed = 0 and p.`id_post_comment` = " . $this->id);
						while ($crow = $comment_qry->fetch_assoc()):
							?>
							<li>
								<div class="comet-avatar">
									<img src="<?php echo validate_image($crow['avatar']) ?>" alt="" height="45" width="45">
								</div>
								<div class="we-comment">
									<div class="coment-head">
										<h5><a href="<?php echo BASE_URL . 'user/user/?id=' . $crow['id_user'] ?>"
												title=""><?php echo $crow['name'] ?></a></h5>
										<span><?php echo $this->relativeDate($crow['post_date']) ?></span>
										<a class="we-reply" href="#" title="Répondre"><i class="fa fa-reply"></i></a>
									</div>
									<p> <?php echo $crow['content_post'] ?>
										<i class="em em-smiley"></i>
									</p>
								</div>
							</li>
						<?php endwhile; ?>
						<!-- Nouveau commentaire -->
						<li class="post-comment" <?php echo ($this->settings->userdata('banned_temporarly') != 0) ? 'style="display: none;"' : '' ?>>
							<div class="comet-avatar">
								<img src="<?php echo validate_image($this->settings->userdata('avatar')) ?>" alt="" height="45"
									width="45">
							</div>

							<div class="post-comt-box">
								<form action="">
									<textarea class="comment-field" data-id='<?= $this->id ?>'
										placeholder="Ecrire un commentaire"></textarea>

									<button type="button" title="Commenter" class="submit-comment"><i class="fa fa-paper-plane"
											style="color:blue;"></i></a></button>
								</form>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<?php

	}//fin méthode

	function display_to_html_owner($isMySpace)
	{

		//conversion temps "database" vers timestamp UNIX
		$timestamp = strtotime($this->postDate);

		//Récupération des informations du propriétaire du post
		$ownerQuery = "SELECT * FROM `user` WHERE id_user = " . $this->idAuthor;
		$ownerResult = $this->conn->query($ownerQuery);
		if ($ownerResult->num_rows > 0) {
			$owner = $ownerResult->fetch_assoc();
		}
		$isOwnerBannedTmp = ($owner['banned_temporarly'] != 0) ? true : false;
		if ($isOwnerBannedTmp) {
			$process = $this->conn->query("SELECT * FROM interdiction WHERE id_user = '{$owner['id_user']}' AND actif = 0 AND code_interdiction_type = '{$this->BANNED_TEMPORALY}'");
			if ($process->num_rows > 0) {
				$data = $process->fetch_array();
				$banRemainedTime = $this->remainTime($data['interdiction_date'], $data['delay']);
			} else {
				$banRemainedTime = 0;
			}
		}

		//Compter le nombre de commentaire 
		//SELECT COUNT(id_post) FROM post where id_post_comment = 25;
		$commentQuery = "SELECT COUNT(id_post) as comments FROM post where removed = 0 and id_post_comment = " . $this->id;
		$commentResult = $this->conn->query($commentQuery);
		if ($commentResult->num_rows != 0) {
			$comments = $commentResult->fetch_assoc();
		}

		//Vérifier si l'utilisateur connecté à liké le post
		$idUser = $this->settings->userdata('id_user');
		$isLike = false;
		$likeQuery = $this->conn->query("SELECT id_post_like FROM `post_like` where id_post = $this->id and id_user = $idUser")->num_rows > 0;

		//Vérifier si l'utilisateur connecté suit ou pas l'autheur du post qui n'est pas lui
		$ownerFollowers = $this->conn->query("SELECT * FROM followship f JOIN post p ON f.id_user_following = p.id_user WHERE p.id_user = $this->idAuthor AND p.id_user != '{$this->settings->userdata('id_user')}' AND f.id_user_follower = '{$this->settings->userdata('id_user')}' ")->num_rows;

		//Vérifier si un post a été marqué comme sensible
		$sensitive = "SENSIBLE";
		$isSensitive = $this->conn->query("SELECT * FROM notification n INNER JOIN interdiction i ON n.id_interdiction = i.id_interdiction WHERE n.id_post = {$this->id} AND i.code_interdiction_type = '{$sensitive}' AND i.actif = 0 ")->num_rows > 0;


		//Sortir ("echo") le HTML suivant :
		?>

		<div class="central-meta item">
			<div class="user-post">
				<div class="friend-info">
					<figure>
						<img src="<?php echo validate_image($owner['avatar']) ?>" alt=""
							class="avatar-img img-thumbnail rounded-circle p-0">
					</figure>
					<div class="friend-name post" data-id="<?= $this->id ?>">
						<ins><a href="<?php echo BASE_URL . 'user/user/?id=' . $owner['id_user'] ?>"
								title=""><?php echo $owner['firstname'] . ' ' . $owner['lastname']; ?></a><span
								class="text-danger"><?php echo $isOwnerBannedTmp ? ' (Banni temporairement : à peu près ' . $banRemainedTime . ' heures restants)' : '' ?></span></ins>
						<span><?php echo $this->displayUsername($owner['username']) ?></span>
						<span>publié le
							<?php echo date("d/m/y à h:i:s", $timestamp) . " (" . $this->relativeDate($this->postDate) . ")"; ?></span>
					</div>

					<?php if ($isSensitive): ?>
						<div class="description sensitive text-center">
							<a id="showPost" href="javascript:void(0)" class="btn btn-danger">Post sensible et potentiellement
								choquant. Voir quand même en cliquant ici.</a>
						</div>
					<?php endif; ?>
					<div id="originalContent" class="render" <?php echo $isSensitive ? 'style="display: none;"' : '' ?>>
						<div class="description post" data-id="<?= $this->id ?>">
							<p>
								<?php echo $this->content; ?>
							</p>
						</div>
						<div class="post-meta">
							<?php if (!empty($this->imgPath)) { ?>
								<?php if (!filter_var($this->imgPath, FILTER_VALIDATE_URL)): ?>
									<img src="<?php echo validate_image($this->imgPath) ?>" alt="" class="post"
										data-id="<?= $this->id ?>">
								<?php else: ?>
									<iframe width="" height="315" src="<?php echo $this->imgPath ?>" allow="autoplay;"
										allowfullscreen></iframe>
								<?php endif; ?>
							<?php } ?>
						</div>
					</div>
					<div class="we-video-info">
						<ul>
							<li>
								<span class="views" data-toggle="tooltip" title="Vues">
									<i class="fa fa-eye"></i>
									<ins><?php echo $this->format_large_number($this->views); ?></ins>
								</span>
							</li>
							<li>
								<span class="comment post_comments" data-id="<?= $this->id ?>" data-toggle="tooltip"
									title="Commentaire">
									<i class="fa fa-comments-o"></i>
									<ins
										class="comment-count"><?php echo $this->format_large_number($comments['comments']); ?></ins>
								</span>
							</li>
							<li>
								<?php if (isset($likeQuery) && !!$likeQuery) { ?>
									<a>
										<span class="like_post" data-toggle="tooltip" title="J'aime/J'aime pas" data-like='true'
											data-id="<?= $this->id ?>">
											<i class="fa fa-heart text-danger"></i>
											<ins class="like-count"><?= $this->format_large_number($this->likes); ?></ins>
										</span>
									</a>
								<?php } else { ?>
									<a>
										<span class="like_post" data-toggle="tooltip" title="J'aime/J'aime pas" data-like='false'
											class="like_post" data-id="<?= $this->id ?>">
											<i class="far fa-heart"></i>
											<ins class="like-count"><?= $this->format_large_number($this->likes); ?></ins>
										</span>
									</a>
								<?php } ?>

							</li>

							<li <?php echo ($this->settings->userdata('banned_temporarly') != 0) ? 'style="display: none;"' : '' ?>>
								<?php if ($this->idAuthor != $this->settings->userdata('id_user')): ?>
									<?php if ($ownerFollowers == 0): ?>
										<a href="javascript:void(0)" class="follow" title="" data-ripple=""
											data-id='<?= $this->idAuthor ?>' data-follow='false'>
											<span data-toggle="tooltip" title="Suivre">
												<i class="fa fa-plus-circle text-primary"></i>
												<ins></ins>
											</span>
										</a>

									<?php else: ?>
										<a href="javascript:void(0)" class="follow" title="" data-ripple=""
											data-id='<?= $this->idAuthor ?>' data-follow='true'>
											<span data-toggle="tooltip" title="Ne plus suivre">
												<i class="fa fa-minus-circle text-danger"></i>
												<ins></ins>
											</span>
										</a>

									<?php endif; ?>
								<?php endif; ?>
							</li>

							<!-- <li class="social-media">
									<div class="menu">
										<div class="btn trigger"><i class="fa fa-share-alt"></i></div>

										<div class="rotater">
											<div class="btn btn-icon"><a href="#" title=""><i class="fa fa-facebook"></i></a>
											</div>
										</div>
										<div class="rotater">
											<div class="btn btn-icon"><a href="#" title=""><i class="fa fa-twitter"></i></a>
											</div>
										</div>
										<div class="rotater">
											<div class="btn btn-icon"><a href="#" title=""><i class="fa fa-instagram"></i></a>
											</div>
										</div>
									</div>
								</li> -->
						</ul>

					</div>
				</div>

				<!-- Affichage des commentaires -->
				<?php if (!$isMySpace): ?>
					<div class="coment-area">
						<ul class="we-comet">
							<?php
							$comment_qry = $this->conn->query("SELECT p.*, concat(u.firstname, ' ',u.lastname) as `name` , u.avatar FROM `post` p inner join `user` u on p.id_user = u.id_user WHERE p.removed = 0 and p.`id_post_comment` = " . $this->id);
							while ($crow = $comment_qry->fetch_assoc()):
								?>
								<li>
									<div class="comet-avatar">
										<img src="<?php echo validate_image($crow['avatar']) ?>" alt="" height="50" width="50">
									</div>
									<div class="we-comment">
										<div class="coment-head">
											<h5><a href="<?php echo BASE_URL . 'user/user/?id=' . $crow['id_user'] ?>"
													title=""><?php echo $crow['name'] ?></a></h5>
											<span><?php echo $this->relativeDate($crow['post_date']) ?></span>
											<a class="we-reply" href="#" title="Répondre"><i class="fa fa-reply"></i></a>
										</div>
										<p> <?php echo $crow['content_post'] ?>
											<i class="em em-smiley"></i>
										</p>
									</div>
								</li>
							<?php endwhile; ?>
							<!-- Nouveau commentaire -->
							<li class="post-comment">
								<div class="comet-avatar">
									<img src="<?php echo validate_image($this->settings->userdata('avatar')) ?>" alt="">
								</div>

								<div class="post-comt-box">
									<form action="">
										<textarea class="comment-field" data-id='<?= $this->id ?>'
											placeholder="Ecrire un commentaire"></textarea>

										<button type="button" title="Commenter" class="submit-comment"><i class="fa fa-paper-plane"
												style="color:blue;"></i></a></button>
									</form>
								</div>
							</li>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php

	}//fin méthode

	function display_to_html_notif()
	{

		//conversion temps "database" vers timestamp UNIX
		$timestamp = strtotime($this->postDate);

		//Récupération des informations du propriétaire du post
		$ownerQuery = "SELECT * FROM `user` WHERE id_user = " . $this->idAuthor;
		$ownerResult = $this->conn->query($ownerQuery);
		if ($ownerResult->num_rows > 0) {
			$owner = $ownerResult->fetch_assoc();
		}

		//Compter le nombre de commentaire 
		//SELECT COUNT(id_post) FROM post where id_post_comment = 25;
		$commentQuery = "SELECT COUNT(id_post) as comments FROM post where removed = 0 and id_post_comment = " . $this->id;
		$commentResult = $this->conn->query($commentQuery);
		if ($commentResult->num_rows != 0) {
			$comments = $commentResult->fetch_assoc();
		}

		//Vérifier si l'utilisateur connecté à liké le post
		$idUser = $this->settings->userdata('id_user');
		$isLike = false;
		$likeQuery = $this->conn->query("SELECT id_post_like FROM `post_like` where id_post = $this->id and id_user = $idUser")->num_rows > 0;

		//Vérifier si un post a été marqué comme sensible
		$sensitive = "SENSIBLE";
		$isSensitive = $this->conn->query("SELECT * FROM notification n INNER JOIN interdiction i ON n.id_interdiction = i.id_interdiction WHERE n.id_post = {$this->id} AND i.code_interdiction_type = '{$sensitive}' AND i.actif = 0 ")->num_rows > 0;

		//Sortir ("echo") le HTML suivant :
		?>

		<div class="central-meta item">
			<div class="user-post">
				<div class="friend-info">
					<figure>
						<img src="<?php echo validate_image($owner['avatar']) ?>" alt=""
							class="avatar-img img-thumbnail rounded-circle p-0">
					</figure>
					<div class="friend-name post" data-id="<?= $this->id ?>">
						<ins><a href="<?php echo BASE_URL . 'user/user/?id=' . $owner['id_user'] ?>"
								title=""><?php echo $owner['firstname'] . ' ' . $owner['lastname']; ?></a></ins>
						<span><?php echo $this->displayUsername($owner['username']) ?></span>
						<span>publié le
							<?php echo date("d/m/y à h:i:s", $timestamp) . " (" . $this->relativeDate($this->postDate) . ")"; ?></span>
					</div>
					<?php if ($isSensitive): ?>
						<div class="description sensitive text-center">
							<a id="showPost" href="javascript:void(0)" class="btn btn-danger">Post sensible et potentiellement
								choquant. Voir quand même en cliquant ici.</a>
						</div>
					<?php endif; ?>
					<div id="originalContent" class="render" <?php echo $isSensitive ? 'style="display: none;"' : '' ?>>
						<div class="description post" data-id="<?= $this->id ?>">
							<p>
								<?php echo $this->content; ?>
							</p>
						</div>
						<div class="post-meta">
							<?php if (!empty($this->imgPath)) { ?>
								<?php if (!filter_var($this->imgPath, FILTER_VALIDATE_URL)): ?>
									<img src="<?php echo validate_image($this->imgPath) ?>" alt="" class="post"
										data-id="<?= $this->id ?>">
								<?php else: ?>
									<iframe width="" height="315" src="<?php echo $this->imgPath ?>" allow="autoplay;"
										allowfullscreen></iframe>
								<?php endif; ?>
							<?php } ?>
						</div>
					</div>

					<div class="we-video-info">
						<ul>
							<li>
								<span class="views" data-toggle="tooltip" title="Vues">
									<i class="fa fa-eye"></i>
									<ins><?php echo $this->format_large_number($this->views); ?></ins>
								</span>
							</li>
							<li>
								<span class="comment post_comments" data-id="<?= $this->id ?>" data-toggle="tooltip"
									title="Commentaire">
									<i class="fa fa-comments-o"></i>
									<ins
										class="comment-count"><?php echo $this->format_large_number($comments['comments']); ?></ins>
								</span>
							</li>
							<li>
								<?php if (isset($likeQuery) && !!$likeQuery) { ?>
									<a>
										<span class="like_post" data-toggle="tooltip" title="J'aime/J'aime pas" data-like='true'
											data-id="<?= $this->id ?>">
											<i class="fa fa-heart text-danger"></i>
											<ins class="like-count"><?= $this->format_large_number($this->likes); ?></ins>
										</span>
									</a>
								<?php } else { ?>
									<a>
										<span class="like_post" data-toggle="tooltip" title="J'aime/J'aime pas" data-like='false'
											class="like_post" data-id="<?= $this->id ?>">
											<i class="far fa-heart"></i>
											<ins class="like-count"><?= $this->format_large_number($this->likes); ?></ins>
										</span>
									</a>
								<?php } ?>

							</li>

							<!-- <li class="social-media">
									<div class="menu">
										<div class="btn trigger"><i class="fa fa-share-alt"></i></div>

										<div class="rotater">
											<div class="btn btn-icon"><a href="#" title=""><i class="fa fa-facebook"></i></a>
											</div>
										</div>
										<div class="rotater">
											<div class="btn btn-icon"><a href="#" title=""><i class="fa fa-twitter"></i></a>
											</div>
										</div>
										<div class="rotater">
											<div class="btn btn-icon"><a href="#" title=""><i class="fa fa-instagram"></i></a>
											</div>
										</div>
									</div>
								</li> -->
						</ul>
					</div>

				</div>
			</div>
		</div>

		<?php

	}//fin méthode


	//Méthode pour obtenir le chemin de l'image thumbnail, à partir du chemin normal
	//--------------------------------------------------------------------------------
	function GetThumbnailPath($originalPath)
	{
		//Nos thmbnails ont le même chemin que le fichier d'origine, mais avec _thumb à la fin.
		//Tous les thumbnails sont des PNG.
		//Donc pour obtenir le nom, il faut enlever l'extension du fichier original, et
		//la remplacer par "_thumb.png"

		//La fonction pathinfo peut découper un chemin de fichier en "morceaux"
		$pathFragments = pathinfo($originalPath);

		//En recombinant les fragments "dirname" et "filename", on a le chemin sans l'extension
		$result = $pathFragments['dirname'] . "/" . $pathFragments['filename'];

		//On rajoute "_thumb.png"
		$result .= "_thumb.png";

		//On retourne le résultat
		return $result;
	}

	/**
	 * Fonction de formatage des nombres
	 */
	function format_large_number($number)
	{
		$suffix = '';
		if ($number >= 1000) {
			$suffix = 'k';
			$number = $number / 1000;
		}
		if ($number >= 1000) {
			$suffix = 'm';
			$number = $number / 1000;
		}
		if ($number >= 1000) {
			$suffix = 'b';
			$number = $number / 1000;
		}

		return number_format($number, 0) . $suffix;
	}

	/**
	 * Fonction de conversion de date
	 */
	function relativeDate($postDate)
	{
		$postTimestamp = is_int($postDate) ? $postDate : strtotime($postDate);

		$timeDifference = time() - $postTimestamp;

		$minute = 60;
		$hour = $minute * 60;
		$day = $hour * 24;
		$month = $day * 30;
		$year = $day * 365;

		if ($timeDifference < $minute) {
			return "à l'instant";
		} elseif ($timeDifference < $hour) {
			$minutesAgo = floor($timeDifference / $minute);
			return " il y'a " . $minutesAgo . " minute" . ($minutesAgo > 1 ? "s" : "");
		} elseif ($timeDifference < $day) {
			$hoursAgo = floor($timeDifference / $hour);
			return " il y'a " . $hoursAgo . " heure" . ($hoursAgo > 1 ? "s" : "");
		} elseif ($timeDifference < $month) {
			$daysAgo = floor($timeDifference / $day);
			return " il y'a " . $daysAgo . " jour" . ($daysAgo > 1 ? "s" : "");
		} elseif ($timeDifference < $year) {
			$monthsAgo = floor($timeDifference / $month);
			return " il y'a " . $monthsAgo . " mois" . ($monthsAgo > 1 ? "s" : "");
		} else {
			$yearsAgo = floor($timeDifference / $year);
			return " il y'a " . $yearsAgo . " an" . ($yearsAgo > 1 ? "s" : "");
		}
	}

	function displayUsername($username)
	{
		return '@' . $username;
	}

	function remainTime($datetime, $hours)
	{
		$datetime = new DateTime($datetime);
		$now = new DateTime();
		$datetime->add(new DateInterval("PT" . $hours . "H"));
		$diff = date_diff($datetime, $now, false);
		$hoursDifference = $diff->days * 24 + $diff->h;
		return $hoursDifference;
		//echo $diff->format("%R%a days");
	}

}

?>